<?php

	namespace model\main;

	/**
	 * Класс для Кеша
	 *
	 * [gist.github.com](https://gist.github.com/Traineratwot/4cc7caa49f5b8951e434b241b84063dd)
	 */
	class Cache
	{
		/**
		 * @param $key      mixed
		 * @param $value    mixed
		 * @param $expire   int
		 * @param $category string
		 * @return mixed
		 */
		public static function setCache($key, $value, $expire = 600, $category = '')
		{
			$name                = \model\main\Cache::getKey($key) . '.cache.php';
			$v                   = var_export($value, 1);
			$expire              = $expire ? $expire + time() : 0;
			$body                = <<<PHP
<?php
	if($expire){if(time()>$expire){unlink(__FILE__);return null;}}
	return $v
?>
PHP;
			$concurrentDirectory = WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR;
			if (!file_exists($concurrentDirectory) or !is_dir($concurrentDirectory)) {
				if (!mkdir($concurrentDirectory, 0777, TRUE) && !is_dir($concurrentDirectory)) {
					throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
				}
			}
			if (is_dir($concurrentDirectory)) {
				file_put_contents($concurrentDirectory . $name, $body);
			}
			return $value;
		}

		/**
		 * @param $a mixed
		 * @return string
		 */
		public static function getKey($a)
		{
			if (is_string($a) and strlen($a) < 32 and preg_match('@\w{1,32}@', $a)) {
				return $a;
			}
			return md5(serialize($a));
		}

		/**
		 * @param $key      mixed
		 * @param $category string
		 * @return mixed|null
		 */
		public static function getCache($key, $category = '')
		{
			if ($category != 'table') {
				if (function_exists('getallheaders')) {
					$headers = getallheaders();
					if ($headers['Cache-Control'] == 'no-cache') {
						return NULL;
					}
				}
			}
			$name = Cache::getKey($key) . '.cache.php';
			if (file_exists(WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name)) {
				return include WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name;
			}
			return NULL;
		}

		/**
		 * @param $key      mixed
		 * @param $category string
		 * @return bool
		 */
		public static function removeCache($key, $category = '')
		{
			$name = Cache::getKey($key) . '.cache.php';
			if (file_exists(WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name)) {
				unlink(WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name);
			}
			return !file_exists(WT_CACHE_PATH . $name);
		}

		public static function __set_state($arr)
		{
			return new Cache();
		}
	}