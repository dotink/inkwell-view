<?php namespace Inkwell
{
	use Closure;
	use ArrayAccess;
	use Dotink\Flourish;

	/**
	* View Class
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
		 * Sub-components views of this view
		 *
		 * @access protected
		 * @var array
		 */
		protected $components = array();


		/**
		 * The container view for this view.
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
		 * The current nesting level for templates in this view
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
		 * @return void
		 */
		public function __construct($root_directory, $assets = NULL)
		{
			$this->root    = $root_directory;
			$this->assets  = $assets;
		}


		/**
		 * Append a subcomponent to a given element
		 *
		 * The component can be a relative or absolute path minus the `.php` extension or it
		 * can be a separate view object.  If the component is an independent view object it
		 * will have its own asset manager, data, and subcomponent set.  If it is a template
		 * path it will share the asset manager, data, and subcomponents of this view.
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
						$component = new self($this->root, $this->assets);

						$component->assets     = &$this->export('assets');
						$component->components = &$this->export('components');
						$component->data       = &$this->export('data');

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
		 * Load the template and optionally mass load components or data
		 *
		 * @access public
		 * @param string $template The template path relative to root or as an absolute path
		 * @param array $component Component array for mass assignment
		 * @param array $data Data array for mass assignment
		 * @return View The called instance for method chaining
		 */
		public function load($template, $components = array(), $data = array())
		{
			$this->template = $template;

			$this->assign($components);
			$this->set($data);

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
		 * Buffer the output of an anonymous function inside a template
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
		 * Tell the view to expand a template using another from inside the template
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
				$container = new self($this->root);

				$container->assets     = &$this->export('assets');
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
	}
}
