<?php

	namespace model\main;
	use Exception;

	if (!defined('WT_CACHE_PATH')) {
		define('WT_CACHE_PATH', './');
	}

	/**
	 * Класс для Кеша
	 *
	 * [gist.github.com](https://gist.github.com/Traineratwot/4cc7caa49f5b8951e434b241b84063dd)
	 */
	class Cache
	{
		/**
		 * Сохраняет результат выполнения callback функции в кеш или возвращает уже за кешированное значение
		 *
		 * @param mixed    $key
		 * @param Callback $function function
		 * @param int      $expire   Время жизни
		 * @param string   $category папка кеша
		 * @param mixed    ...$args
		 * @return mixed|null
		 * @throws Exception
		 */
		public static function call($key, $function, $expire = 600, $category = '', ...$args)
		{
			$result = self::getCache($key, $category);
			if ($result !== NULL) {
				return $result;
			}
			if (is_callable($function)) {
				$args   = func_get_args();
				$args   = array_slice($args, 4);
				$result = call_user_func($function, ...$args);
				if ($result !== NULL) {
					return self::setCache($key, $result, $expire, $category);
				}
				return NULL;
			} else {
				throw new Exception("Is not a function");
			}
			return NULL;
		}

		/**
		 * Сохраняет значение в кеш
		 * @param mixed  $key      ключ
		 * @param mixed  $value    значение
		 * @param int    $expire   Время жизни
		 * @param string $category папка кеша
		 * @return mixed
		 */
		public static function setCache($key, $value, $expire = 600, $category = '')
		{
			$name                = self::getKey($key) . '.cache.php';
			$v                   = var_export($value, 1);
			$expire              = $expire ? $expire + time() : 0;
			$body                = <<<PHP
<?php
	if($expire){if(time()>$expire){unlink(__FILE__);return null;}}
	return $v
?>
PHP;
			$concurrentDirectory = WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR;
			if (!file_exists($concurrentDirectory) || !is_dir($concurrentDirectory)) {
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
		 * Превратить ключ кеша в строку
		 * @param mixed $a
		 * @return string
		 */
		public static function getKey($a)
		{
			if (is_string($a) && strlen($a) < 32 && preg_match('@\w{1,32}@', $a)) {
				return $a;
			}
			return md5(serialize($a));
		}

		/**
		 * Возвращает значение из кеша
		 * @param mixed  $key      ключ
		 * @param string $category папка кеша
		 * @return mixed|null значение
		 */
		public static function getCache($key, $category = '')
		{
			if ($category != 'table') {
				//если установлен заголовок отключить кеш отключаем кеш
				if (function_exists('getallheaders')) {
					$headers = getallheaders();
					if ($headers['Cache-Control'] == 'no-cache') {
						return NULL;
					}
				}
			}
			$name = self::getKey($key) . '.cache.php';
			if (file_exists(WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name)) {
				return include WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name;
			}
			return NULL;
		}

		/**
		 * Удаляет файл кеша
		 * @param mixed  $key      ключ
		 * @param string $category папка кеша
		 * @return bool
		 */
		public static function removeCache($key, $category = '')
		{
			$name = self::getKey($key) . '.cache.php';
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