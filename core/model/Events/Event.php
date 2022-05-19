<?php

	namespace model\Events;

	use Exception;
	use model\main\Utilities;

	class Event
	{
		private static array $plugins = [];

		/**
		 * Emit event
		 * @param string $event
		 * @param mixed  $data
		 * @param string $category
		 * @return null
		 * @throws Exception
		 */
		public static function emit(string $event, $data = [], string $category = '')
		{
			$plugin = self::load($event, $category);
			return self::execute($plugin, $data);
		}

		/**
		 * Emit event
		 * @param string $event
		 * @param mixed  $data
		 * @param string $category
		 * @return null
		 * @throws Exception
		 */
		public static function trigger(string $event, $data = [], string $category = '')
		{
			return self::emit($event, $data, $category);
		}

		public static function registerEvent(string $event, callable $callback, string $category = '')
		{
			self::$plugins[$category][$event] = $callback;
		}

		/**
		 * @throws Exception
		 */
		private static function load(string $event, string $category)
		{
			if (isset(self::$plugins[$category][$event])) {
				return self::$plugins[$category][$event];
			}

			$class = Utilities::findPath(WT_PLUGINS_PATH . $category . DIRECTORY_SEPARATOR . $event.'.php');
			if(file_exists($class)) {
				$cls = include $class;
				if (is_string($cls) && class_exists($cls)) {
					self::$plugins[$category][$event] = $cls;
					return $cls;
				}
			}
			return FALSE;
		}

		/**
		 * @param string|callable $plugin
		 * @param mixed           $data
		 * @return mixed
		 */
		private static function execute($plugin, $data = [])
		{
			if (is_string($plugin) && class_exists($plugin)) {
				return (new $plugin())->run($data);
			}
			if (is_callable($plugin)) {
				return $plugin($data);
			}
			return NULL;
		}
	}