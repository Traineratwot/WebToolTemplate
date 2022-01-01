<?php

	namespace core\model;
	//определяем основные пути
	error_reporting(E_ERROR);
	define('WT_BASE_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
	define('WT_CORE_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR);
	define('WT_CACHE_PATH', WT_CORE_PATH . 'cache' . DIRECTORY_SEPARATOR);
	define('WT_MODEL_PATH', WT_CORE_PATH . 'model' . DIRECTORY_SEPARATOR);
	define('WT_VENDOR_PATH', WT_CORE_PATH . 'vendor' . DIRECTORY_SEPARATOR);
	define('WT_PAGES_PATH', WT_CORE_PATH . 'pages' . DIRECTORY_SEPARATOR);
	define('WT_VIEWS_PATH', WT_CORE_PATH . 'views' . DIRECTORY_SEPARATOR);
	define('WT_CLASSES_PATH', WT_CORE_PATH . 'classes' . DIRECTORY_SEPARATOR);
	define('WT_TEMPLATES_PATH', WT_CORE_PATH . 'templates' . DIRECTORY_SEPARATOR);
	define('WT_AJAX_PATH', WT_CORE_PATH . 'ajax' . DIRECTORY_SEPARATOR);
	define('WT_ASSETS_PATH', WT_BASE_PATH . 'assets' . DIRECTORY_SEPARATOR);
	define('WT_IMAGES_PATH', WT_ASSETS_PATH . 'images' . DIRECTORY_SEPARATOR);
	//ВНЕШНИЙ URL
	define('WT_DOMAIN_URL', $_SERVER['SERVER_NAME'] ?: $_SERVER['HTTP_HOST']);
	define('WT_NODE_URL', WT_DOMAIN_URL . '/node_modules' . '/');
	//определяем подключение к своей базе
	define('WT_HOST_DB', WT_CORE_PATH . 'databases/database.db');
	define('WT_PORT_DB', '');
	define('WT_DATABASE_DB', '');
	define('WT_TYPE_DB', 'sqlite');
	define('WT_USER_DB', '');
	define('WT_PASS_DB', '');
	define('WT_DSN_DB', WT_TYPE_DB . ":" . WT_HOST_DB);
//	define('WT_HOST_DB', 'localhost');
//	define('WT_PORT_DB', '3306');
//	define('WT_DATABASE_DB', 'wt');
//	define('WT_TYPE_DB', 'mysql');
//	define('WT_USER_DB', 'root');
//	define('WT_PASS_DB', '');
//	define('WT_DSN_DB', WT_TYPE_DB . ":host=" . WT_HOST_DB . ";port=" . WT_PORT_DB . ";dbname=" . WT_DATABASE_DB);
	//Настройки шаблонизатора
	define('WT_SMARTY_TEMPLATE_PATH', WT_CORE_PATH . 'templates');
	define('WT_SMARTY_COMPILE_PATH', WT_CACHE_PATH . 'smarty/compile');
	define('WT_SMARTY_CONFIG_PATH', WT_CACHE_PATH . 'smarty/config');
	define('WT_SMARTY_CACHE_PATH', WT_CACHE_PATH . 'smarty/cache');
	define('WT_SMARTY_PLUGINS_PATH', WT_MODEL_PATH . 'smarty' . DIRECTORY_SEPARATOR . 'plugins');

	//настройка Почты
	define('WT_FROM_EMAIL_MAIL', 'admin@example.com');
	define('WT_FROM_NAME_MAIL', 'admin');
	define('WT_SMTP_MAIL', FALSE); //включить SMTP
	define('WT_HOST_MAIL', 'smtp.example.com');
	define('WT_AUTH_MAIL', FALSE);//включить Авторизацию
	define('WT_USERNAME_MAIL', 'admin');
	define('WT_PASSWORD_MAIL', 'admin');
	define('WT_SECURE_MAIL', 'ssl');//тип шифрования
	define('WT_PORT_MAIL', '465');
	//настройка Локализации
	define('WT_LOCALE_DOMAIN', 'messages');
	define('WT_LOCALE_PATH', WT_BASE_PATH . 'locale' . DIRECTORY_SEPARATOR);

	if (!function_exists('getSystem')) {
		function getSystem()
		{
			$sys = mb_strtolower(php_uname('s'));
			if (strpos($sys, 'windows') !== FALSE) {
				return 'win';
			}
			if (strpos($sys, 'linux') !== FALSE) {
				return 'nix';
			}
			return 'nix';
		}
	}
	/**
	 * Пользовательская функция возвращяющяя язык для установки локали на основе url
	 * вы можете ее менять
	 * @return false|string
	 */
	if (!function_exists('WT_LOCALE_SELECT_FUNCTION')) {
		function WT_LOCALE_SELECT_FUNCTION()
		{
			preg_match('/^(.{1,3})?\..*$/', $_SERVER['HTTP_HOST'], $math);
			if (!isset($math[1])) {
				if (class_exists('\Locale')) {
					return Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
				} else {
					return FALSE;
				}
			} else {
				return $math[1];
			}
		}
	}
	define('WT_TYPE_SYSTEM', getSystem());