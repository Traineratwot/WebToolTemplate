<?php

	namespace model\Events;

	use Exception;
	use model\main\Utilities;
	use Traineratwot\config\Config;

	class Event
	{
		private static array $plugins = [];

		/**
		 * Emit event
		 * @param string $event
		 * @param mixed  $data
		 * @param string $category
		 * @return null
		 *
		 */
		public static function trigger(string $event, $data = [], string $category = '')
		{
			return self::emit($event, $data, $category);
		}

		/**
		 * Emit event
		 * @param string      $event
		 * @param string|null $category
		 * @param             ...$args
		 * @return null
		 */
		public static function emit(string $event, string $category = NULL, ...$args)
		{
			if (!$category) {
				$category = '';
			}
			$plugin = self::load($event, $category);
			return self::execute($plugin, ...$args);
		}

		/**
		 *
		 */
		private static function load(string $event, string $category)
		{
			try {
				if (isset(self::$plugins[$category][$event])) {
					return self::$plugins[$category][$event];
				}

				$class = Utilities::findPath(Config::get('PLUGINS_PATH') . $category . DIRECTORY_SEPARATOR . $event . '.php');
				if (file_exists($class)) {
					$cls = include $class;
					if (is_string($cls) && class_exists($cls)) {
						self::$plugins[$category][$event] = $cls;
						return $cls;
					}
				}
			} catch (Exception $e) {

			}
			return FALSE;
		}

		/**
		 * @param callable|string $plugin
		 * @param mixed           ...$args
		 * @return mixed
		 */
		private static function execute(callable|string $plugin, ...$args)
		{
			if (is_string($plugin) && class_exists($plugin)) {
				/** @var Plugin $plugin */
				return (new $plugin())->run(...$args);
			}
			if (is_callable($plugin)) {
				return $plugin($data);
			}
			return NULL;
		}

		public static function registerEvent(string $event, callable $callback, string $category = '')
		{
			self::$plugins[$category][$event] = $callback;
		}
	}