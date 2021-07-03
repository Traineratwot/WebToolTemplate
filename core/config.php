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
	define('local_HOST', 'localhost');
	define('local_PORT', '3306');
	define('local_DATABASE', '');
	define('local_USER', '');
	define('local_PASS', '');
	define('local_DSN', "mysql:host=" . local_HOST . ";dbname=" . local_DATABASE . ";charset=utf8mb4");
	foreach ([BASE_PATH, CORE_PATH, CACHE_PATH, VENDOR_PATH, CLASSES_PATH, ASSETS_PATH, IMAGES_PATH] as $v) {
		if (!file_exists($v) or !is_dir($v)) {
			if (!mkdir($v, 0777, 1) && !is_dir($v)) {
				throw new \RuntimeException(sprintf('Directory "%s" was not created', $v));
			}
		}
	}