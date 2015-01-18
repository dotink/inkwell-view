<?php namespace Inkwell
{
	use App;
	use ArrayObject;
	use Dotink\Flourish;
	use Travesable;
	use ArrayAccess;
	use Serializable;
	use Assetic\Asset;
	use IW\REGEX;

	/**
	* View Class
	*
	* @copyright Copyright (c) 2012, Matthew J. Sahagian
	* @author Matthew J. Sahagian [mjs] <gent@dotink.org>
	*
	* @license Please reference the LICENSE.txt file at the root of this distribution
	*
	* @package Dotink\Inkwell
	*/
	class View // implements Traversable, ArrayAccess, Serializable
	{
		/**
		 *
		 */
		protected $assets = NULL;


		/**
		 *
		 */
		protected $container = NULL;


		/**
		 *
		 */
		protected $data = array();


		/**
		 *
		 */
		protected $components = array();


		/**
		 *
		 */
		protected $template = array();


		/**
		 *
		 */
		protected $parent = NULL;


		/**
		 *
		 */


		/**
		 *
		 */
		protected $view = NULL;


		/**
		 *
		 */
		public function __construct($root_directory, $assets = NULL)
		{
			$this->root    = $root_directory;
			$this->assets  = $assets;
		}


		/**
		 *
		 */
		public function __tostring()
		{
			return $this->compose();
		}


		/**
		 *
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

						$component->load($template);
					}

					$component->container         = $this;
					$this->components[$element][] = $component;
				}
			}

			return $this;
		}


		/**
		 *
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
		 *
		 */
		public function attach($source, $attributes = array())
		{
			//
			// This is a placeholder proxy method for a decent asset manager
			//
		}


		/**
		 *
		 */
		public function compose()
		{
			if (!$this->view) {
				$expand = (boolean) !$this->container;

				$this->view = $this->buffer(function() {
					$this->inject($this->template);
				});

				if ($expand) {
					for ($container = $this->container; $container; $container = $view->container) {
						$this->view = $container->compose();
					}
				}
			}

			return $this->view;
		}


		/**
		 *
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
		 *
		 */
		public function has($key)
		{
			return array_key_exists($element, $this);
		}


		/**
		 *
		 */
		public function load($template, $components = array(), $data = array())
		{
			$this->template = $template;

			$this->assign($components);
			$this->set($data);

			return $this;
		}


		/**
		 *
		 */
		public function offsetGet($key)
		{
			return parent::offsetExists($key)
				? parent::offsetGet($key)
				: NULL;
		}


		/**
		 *
		 */
		public function offsetSet($key, $value)
		{
			return $value !== NULL
				? parent::offsetSet($key, $value)
				: parent::offsetUnset($key);
		}


		/**
		 *
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
		 *
		 */
		private function buffer(\Closure $closure)
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
		 *
		 */
		private function expand($element, $template)
		{
			$container = new self($this->root);

			$container->assets     = &$this->export('assets');
			$container->components = &$this->export('components');
			$container->data       = &$this->export('data');

			$container->load($template);
			$container->append($element, $this);
		}


		/**
		 *
		 */
		private function &export($property)
		{
				return $this->$property;
		}


		/**
		 *
		 */
		private function inject($template)
		{
			$template = !preg_match('#^(/|\\\\|[a-z]:(\\\\|/)|\\\\|//)#i', $template)
				? $this->root . DIRECTORY_SEPARATOR . $template . '.php'
				: $template;

			include $template;
		}


		/**
		 *
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
