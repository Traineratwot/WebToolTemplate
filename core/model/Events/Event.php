<?php

	namespace model\Events;

	use Exception;
	use model\main\Err;
	use model\main\Utilities;
	use ReflectionClass;
	use RuntimeException;
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
		 * @return array
		 */
		public static function emit(string $event, string $category = NULL, ...$args)
		: array
		{
			if (!$category) {
				$category = '';
			}
			$output  = [];
			$plugins = self::load($event, $category);
			foreach ($plugins as $plugin) {
				$getNamespace          = '';
				$output[$getNamespace] = self::execute($getNamespace, $plugin, ...$args);
			}
			return $output;
		}

		/**
		 *
		 */
		private static function load(string $event, string $category)
		{
			try {
				if (!empty(self::$plugins[$category][$event])) {
					return self::$plugins[$category][$event];
				}

				$class = Utilities::findPath(Config::get('PLUGINS_PATH') . $category . DIRECTORY_SEPARATOR . $event . '.php');
				if (file_exists($class)) {
					$cls = include $class;
					if (is_string($cls) && class_exists($cls)) {
						self::$plugins[$category][$event][] = $cls;
					}
				}
				$pattern = "*/classes/plugins/$event.php";
				if ($category) {
					$pattern = "*/classes/plugins/$category/$event.php";
				}
				$files = Utilities::glob(Config::get('COMPONENTS_PATH'), $pattern);
				foreach ($files as $file) {
					if (file_exists($file)) {
						$cls = include $file;
						if (is_string($cls) && class_exists($cls)) {
							self::$plugins[$category][$event][] = $cls;
						}
					}
				}
				return self::$plugins[$category][$event];
			} catch (Exception $e) {
				Err::fatal($e->getMessage(), NULL, NULL, $e);
			}
			return FALSE;
		}

		/**
		 * @param string          $getNamespace
		 * @param callable|string $plugin
		 * @param mixed           ...$args
		 * @return mixed
		 */
		private static function execute(string &$getNamespace, callable|string $plugin, ...$args)
		{
			if (is_string($plugin) && class_exists($plugin)) {
				$p = new $plugin();
				if ($p instanceof Plugin) {
					$getNamespace = (new ReflectionClass($plugin))->getNamespaceName();
					return $p->run(...$args);
				}
				throw new RuntimeException("Invalid plugin '$plugin'");
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