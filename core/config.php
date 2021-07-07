<?php

	namespace core;
	//определяем основные пути
	define('BASE_PATH', realpath(dirname(__DIR__)) . '/');
	define('CORE_PATH', realpath(__DIR__) . '/');
	define('CACHE_PATH', CORE_PATH . 'cache/');
	define('VENDOR_PATH', BASE_PATH . 'vendor/');
	define('PAGES_PATH', BASE_PATH . 'pages/');
	define('CLASSES_PATH', CORE_PATH . 'classes/');
	define('ASSETS_PATH', BASE_PATH . 'assets/');
	define('IMAGES_PATH', ASSETS_PATH . 'images/');
	//ВНЕШНИЙ URL
	define('DOMAIN_URL', 'stat.aytour.ru');
	//определяем подключение к своей базе
	define('DB_HOST', CORE_PATH . 'databases/database.db');
	define('DB_PORT', '');
	define('DB_DATABASE', '');
	define('DB_TYPE', 'sqlite');
	define('DB_USER', '');
	define('DB_PASS', '240997');
	define('DB_DSN', DB_TYPE . ":" . local_HOST);
	//Настройки шаблонизатора
	define('SMARTY_TEMPLATE', CORE_PATH . 'templates');
	define('SMARTY_COMPILE', CORE_PATH . 'smarty/compile');
	define('SMARTY_CONFIG', CORE_PATH . 'smarty/config');
	define('SMARTY_CACHE', CORE_PATH . 'smarty/cache');

	foreach ([
		         BASE_PATH,
		         CORE_PATH,
		         CACHE_PATH,
		         VENDOR_PATH,
		         CLASSES_PATH,
		         ASSETS_PATH,
		         IMAGES_PATH,
		         SMARTY_TEMPLATE,
		         SMARTY_COMPILE,
		         SMARTY_CONFIG,
		         SMARTY_CACHE,
	         ] as $v) {
		if (!file_exists($v) or !is_dir($v)) {
			if (!mkdir($v, 0777, 1) && !is_dir($v)) {
				throw new \RuntimeException(sprintf('Directory "%s" was not created', $v));
			}
		}
	}
	if (!file_exists(VENDOR_PATH . 'autoload.php')) {
		exec('composer update');
	}
	if (!file_exists(BASE_PATH . 'node_modules')) {
		exec('npm update');
	}