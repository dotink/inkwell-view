<?php namespace Inkwell
{
	use Closure;
	use ArrayAccess;
	use Dotink\Flourish;
	use ReflectionFunction;

	/**
	* Views provide a simple object interface for data and template aggregation.
	*
	* @copyright Copyright (c) 2015, Matthew J. Sahagian
	* @author Matthew J. Sahagian [mjs] <msahagian@dotink.org>
	*
	* @license Please reference the LICENSE.md file at the root of this distribution
	*
	* @package Dotink\Inkwell
	*/
	class View implements ArrayAccess
	{
		/**
		 * Asset manager instance
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $assets = NULL;


		/**
		 * Sub-components of this view
		 *
		 * @access protected
		 * @var array
		 */
		protected $components = array();


		/**
		 * The container for this view.
		 *
		 * @access protected
		 * @var View
		 */
		protected $container = NULL;


		/**
		 * The view data accessible via ArrayAccess
		 *
		 * @access protected
		 * @var array
		 */
		protected $data = array();


		/**
		 * The current filter, selected by format type at time of injection
		 *
		 * @access protected
		 * @var callable
		 */
		protected $filter = NULL;


		/**
		 * Available filters keyed by format type
		 *
		 * @access protected
		 * @var array
		 */
		protected $filters = array();


		/**
		 * The
		 */
		protected $format = NULL;


		/**
		 * The current nesting level for templates in this view object
		 *
		 * @access protected
		 * @var integer
		 */
		protected $level = -1;


		/**
		 * The template location, relative to root or as absolute path
		 *
		 * @access protected
		 * @var string
		 */
		protected $template = NULL;


		/**
		 * The rendered view including rendered component views
		 *
		 * @access protected
		 * @var string
		 */
		protected $view = NULL;


		/**
		 * Create a new view object
		 *
		 * @access public
		 * @param string $root_directory The root directory for templates
		 * @param mixed $assets The asset manager for adding CSS / JSON
		 * @param array $filters Array of callable filters, keyed by format type
		 * @return void
		 */
		public function __construct($root_directory, $assets = NULL, array $filters = array())
		{
			$this->root    = $root_directory;
			$this->assets  = $assets;

			foreach ($filters as $format => $filter) {
				$this->filter($format, $filter);
			}
		}


		/**
		 * Dynamically access view content by calling the view as a function
		 *
		 * This method will resolve data via JS object syntax, generate closure wrappers with
		 * variable injection, and allow for filtering all data access via it.
		 *
		 * The `node` parameter should consist either of a string of resolveable data properties
		 * such as 'user.company.name', or a closure with mappable parameters.
		 *
		 * @access public
		 * @param string|Closure $node A unit with resolveable data names
		 * @return mixed The data resolved on the view
		 */
		public function __invoke($node)
		{
			if ($node instanceof Closure) {
				return $this->invokeClosure($node);

			} else {
				return $this->invokeString((string) $node);
			}
		}


		/**
		 * Append a subcomponent to a given element
		 *
		 * The component can be a relative or absolute path minus the `.php` extension or it
		 * can be a separate view object.  If the component is an independent view object it
		 * will have its own asset manager, filters, data, and subcomponent set.  If it is a
		 * template path it will share the asset manager and filters and receive a copy of the
		 * data, and subcomponents of this view.
		 *
		 * @access public
		 * @param string $element The subcomponent stack to append to
		 * @param string|View $component The subcomponent to append
		 * @return View The called instance for method chaining
		 */
		public function append($element, $component = NULL)
		{
			$elements = !is_array($element)
				? [$element => $component]
				: $element;

			foreach ($elements as $element => $components) {
				if (!isset($this->components[$element])) {
					$this->components[$element] = array();
				}

				if (!is_array($components)) {
					$components = [$components];
				}

				foreach ($components as $component) {
					if (!($component instanceof self)) {
						$template  = $component;
						$component = new static($this->root, $this->assets, $this->filters);

						$component->components = $this->export('components');
						$component->data       = $this->export('data');

						$component->load($template);
					}

					$component->container         = $this;
					$this->components[$element][] = $component;
				}
			}

			return $this;
		}


		/**
		 * Assigns a subcomponent to an element
		 *
		 * Assign a subcomponent, either a `string` or a `View` to given element, overwriting any
		 * existing assignments or previously appended values.  The original subcomponent stack will
		 * be cleared with an empty array.
		 *
		 * @access public
		 * @var string $element The subcomponent stack to assign to
		 * @var string|View $component The subcomponent to assign
		 * @return View The called instance for method chaining
		 */
		public function assign($element, $component = NULL)
		{
			$elements = is_array($element)
				? array_keys($element)
				: [$element];

			foreach ($elements as $element) {
				$this->components[$element] = array();
			}

			return $this->append($element, $component);
		}


		/**
		 * Creates a new view object which shares the root, assets, and filters of this one.
		 *
		 * @access public
		 * @param string $template The template path relative to root or as an absolute path
		 * @param array $data Data array for mass assignment
		 * @param array $component Component array for mass assignment
		 * @return View A newly created view object
		 */
		public function create($template, $data = array(), $components = array())
		{
			$view = new static($this->root, $this->assets, $this->filters);

			return $view->load($template, $data, $components);
		}


		/**
		 * Render the view to a string
		 *
		 * This will compile the view, inserted subcomponents, injected partials, and potentially
		 * expand container views into a final string representation.
		 *
		 * @access public
		 * @return string The rendered view
		 */
		public function compose()
		{
			if (!$this->view) {
				$expand = (boolean) !$this->container;

				$this->view = $this->buffer(function() {
					$this->inject($this->template);
				});

				if ($expand) {
					for ($container = $this->container; $container; $container = $container->container) {
						$this->view = $container->compose();
					}
				}
			}

			return $this->view;
		}


		/**
		 * Add a filter for a particular format
		 *
		 * @access public
		 * @param string $format The format type to use this filter on
		 * @param callable $callback The filter to call
		 */
		public function filter($format, callable $callback)
		{
			$this->filters[strtolower($format)] = $callback;
		}


		/**
		 * Get view data with strict requirements
		 *
		 * Unlike accessing view data via the `ArrayAccess` interface, this method will throw
		 * an exception if the key does not exist and no default is provided.  This is essentially
		 * a mechanism to require data be provided in a given template.
		 *
		 * @access public
		 * @param string $key The key for the data
		 * @param mixed $default A default value if the data does not exist
		 * @return mixed The value of the data
		 * @throws Flourish\ProgrammerException If no default is provided and no data exists
		 */
		public function get($key, $default = NULL)
		{
			if (!$this->has($key) && func_num_args() == 1) {
				throw new Flourish\ProgrammerException(
					'Cannot get data "%s", not set in data and no default provided.',
					$key
				);
			}

			return $this[$key] !== NULL
				? $this[$key]
				: $default;
		}


		/**
		 * Determine if a key exists in the data with strict requirements
		 *
		 * This determines if a piece of data is actually set in the data array as opposed to
		 * the standard `isset` style behavior.
		 *
		 * @access public
		 * @param string $key The key for which to check the data
		 * @return boolean TRUE if the key exists in the data, FALSE otherwise
		 */
		public function has($key)
		{
			return array_key_exists($key, $this->data);
		}


		/**
		 * Load the template and optionally mass load data and components
		 *
		 * @access public
		 * @param string $template The template path relative to root or as an absolute path
		 * @param array $data Data array for mass assignment
		 * @param array $component Component array for mass assignment
		 * @return View The called instance for method chaining
		 */
		public function load($template, $data = array(), $components = array())
		{
			$this->template = $template;
			$this->format   = strtolower(pathinfo($this->template, PATHINFO_EXTENSION));

			$this->set($data);
			$this->assign($components);

			return $this;
		}


		/**
		 * Check if an offset exists
		 *
		 * @see http://php.net/manual/en/class.arrayaccess.php
		 */
		public function offsetExists($key)
		{
			return isset($this->data[$key]);
		}


		/**
		 * Get an offset
		 *
		 * Note that when access through this method, even if a key does not exist in the data
		 * array, you will still receive `NULL` as the data.
		 *
		 * @see http://php.net/manual/en/class.arrayaccess.php
		 */
		public function offsetGet($key)
		{
			return isset($this->data[$key])
				? $this->data[$key]
				: NULL;
		}


		/**
		 * Set an offset
		 *
		 * Note that if you set a particular offset to `NULL` it will be removed from the data
		 * array.
		 *
		 * @see http://php.net/manual/en/class.arrayaccess.php
		 */
		public function offsetSet($key, $value)
		{
			if ($value === NULL) {
				unset($this->data[$key]);
			} else {
				$this->data[$key] = $value;
			}
		}


		/**
		 * Unset an offset
		 *
		 * @see http://php.net/manual/en/class.arrayaccess.php
		 */
		public function offsetUnset($key)
		{
			unset($this->data[$key]);
		}


		/**
		 * Set view data with strict requirements
		 *
		 * Unlike assigning view data via the `ArrayAccess` interface, this method will throw
		 * an exception if the key is already present in the data.  This is essentially
		 * a mechanism to prevent certain data from being overloaded.
		 *
		 * @access public
		 * @param string $key The key for the data
		 * @param mixed $value The value to assign to the data
		 * @return View The called instance for method chaining
		 */
		public function set($key, $value = NULL)
		{
			$keys = !is_array($key)
				? [$key => $value]
				: $key;

			foreach ($keys as $key => $value) {
				if ($this->has($key)) {
					throw new Flourish\ProgrammerException(
						'Cannot set data "%s", data has already been provided.',
						$key
					);
				}

				$this[$key] = $value;
			}

			return $this;
		}


		/**
		 * Buffer the output of a closure inside a template
		 *
		 * @access private
		 * @param Closure $closure The closure for which to buffer output
		 * @return string The buffered output of the function
		 */
		private function buffer(Closure $closure)
		{
			if (ob_start()) {
				$closure();
				return ob_get_clean();
			}

			throw new Flourish\EnvironmentException(
				'Failed to start output buffering'
			);
		}


		/**
		 * Tell the view to expand a template into another at a given subcomponent element
		 *
		 * Templates are only expanded if they are not inserted into an existing container at
		 * the time of composition.  This allows a template to be used as both a partial and a
		 * full page.
		 *
		 * @access private
		 * @param string $element The container template element in which to add the current view
		 * @param string $template The template to load for the containing view
		 * @return View The called instance for method chaining
		 */
		private function expand($element, $template)
		{
			if ($this->level == 0) {
				$container = new static($this->root);

				$container->assets     = &$this->export('assets');
				$container->filters    = &$this->export('filters');
				$container->components = &$this->export('components');
				$container->data       = &$this->export('data');

				$container->load($template);
				$container->append($element, $this);
			}

			return $this;
		}


		/**
		 * Export a private property as a reference
		 *
		 * This is mostly used internally for sharing data or components between templates.
		 *
		 * @access private
		 * @param string $property The property to export
		 * @return reference A reference to the requested property
		 */
		private function &export($property)
		{
				return $this->$property;
		}


		/**
		 * Inject a template directly from within a template
		 *
		 * @access private
		 * @param string $template The template to inject as a partial
		 * @return View The called instance for method chaining
		 */
		private function inject($template)
		{
			$template = !preg_match('#^(/|\\\\|[a-z]:(\\\\|/)|\\\\|//)#i', $template)
				? $this->root . DIRECTORY_SEPARATOR . $template . '.php'
				: $template;

			$this->level++;

			if (isset($this->filters[$this->format])) {
				$this->filter = $this->filters[$this->format];
			}

			require $template;

			$this->level--;

			return $this;
		}


		/**
		 * Insert subcomponents at this position
		 *
		 * @access private
		 * @param string $element The subcomponent stack to insert
		 * @return View The called instance for method chaining
		 */
		private function insert($element)
		{
			$components = isset($this->components[$element])
				? $this->components[$element]
				: array();

			foreach ($components as $component) {
				echo $component->compose();
			}

			return $this;
		}


		/**
		 * Wrap a closure passed to `__invoke()` in order to incorporate it's parameters as data
		 *
		 * @access private
		 * @param Closure $emitter The closure to wrap
		 * @return Closure The wrapping closure with necessary data mapping
		 */
		private function invokeClosure(Closure $emitter)
		{
			$reflection = new ReflectionFunction($emitter);
			$parameters = $reflection->getParameters();
			$data_names = array();

			switch (count($parameters)) {
				case 0:
					break;

				default:
					foreach ($parameters as $parameter) {
						$data_names[] = $parameter->getName();
					}
					break;
			}

			return function() use ($emitter, $data_names) {
				$old_values = array();
				$arguments  = func_get_args();

				foreach ($data_names as $i => $data_name) {
					$old_values[$data_name] = $this[$data_name];
					$this[$data_name]       = $arguments[$i];
				}

				ob_start();
				call_user_func_array($emitter, $arguments);

				foreach ($data_names as $data_name) {
					$this[$data_name] = $old_values[$data_name];
				}

				echo ob_get_clean();
			};
		}


		/**
		 * Resolve a string passed to `__invoke()` in order to filter child data
		 *
		 * @access private
		 * @param string $property The property to retrieve
		 * @return mixed The value of the property, filtered if need be
		 */
		private function invokeString($property)
		{
			$head  = $this;
			$parts = !is_array($property)
				? explode('.', $property)
				: $property;

			foreach ($parts as $part) {
				if ($head instanceof ArrayAccess || is_array($head)) {
					$head = isset($head[$part])
						? $head[$part]
						: NULL;

				} elseif (is_object($head)) {
					if (isset($head->$part)) {
						$head = $head->$part;
					} elseif (is_callable([$head, 'get' . $part])) {
						$head = $head->{ 'get' . $part }();
					} else {
						$head = NULL;
					}
				}

				if (!$head) {
					break;
				}
			}

			return ($filter = $this->filter)
				? $filter($head)
				: $head;
		}
	}
}
