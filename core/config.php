<?php
	//включаем ошибки
	use Traineratwot\cc\Config;

	ini_set('display_errors', 1);
	error_reporting(E_ERROR);
	//определяем основные пути
	define('WT_BASE_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
	define('WT_CORE_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR);
	define('WT_CACHE_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);
	define('WT_MODEL_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR);
	define('WT_VENDOR_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);
	define('WT_PAGES_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR);
	define('WT_VIEWS_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);
	define('WT_CLASSES_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
	define('WT_TEMPLATES_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR);
	define('WT_AJAX_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'ajax' . DIRECTORY_SEPARATOR);
	define('WT_CRON_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR);
	define('WT_ASSETS_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
	define('WT_IMAGES_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR);
	//настройка Cron
	define('WT_PHP_EXEC_CMD', "php"); //команда запуска php скрипта
	//Внешний url
	define('WT_DOMAIN_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/");
	define('WT_NODE_URL', WT_DOMAIN_URL . '/node_modules' . '/');
	//определяем подключение к своей базе
	define('WT_HOST_DB', WT_CORE_PATH . 'databases/database.db');
	define('WT_PORT_DB', '');
	define('WT_DATABASE_DB', '');
	define('WT_TYPE_DB', 'sqlite');
	define('WT_USER_DB', '');
	define('WT_PASS_DB', '');
	define('WT_CHARSET_DB', '');
//	define('WT_HOST_DB', 'localhost');
//	define('WT_PORT_DB', '3306');
//	define('WT_DATABASE_DB', 'wt');
//	define('WT_TYPE_DB', 'mysql');
//	define('WT_USER_DB', 'root');
//	define('WT_PASS_DB', '');
//	define('WT_CHARSET_DB', 'utf8mb4');
	//Настройки шаблонизатора
	define('WT_SMARTY_TEMPLATE_PATH', WT_CORE_PATH . 'templates');
	define('WT_SMARTY_COMPILE_PATH', WT_CACHE_PATH . 'smarty/compile');
	define('WT_SMARTY_CONFIG_PATH', WT_CACHE_PATH . 'smarty/config');
	define('WT_SMARTY_CACHE_PATH', WT_CACHE_PATH . 'smarty/cache');
	define('WT_SMARTY_PLUGINS_PATH', WT_CLASSES_PATH . 'smarty' . DIRECTORY_SEPARATOR . 'plugins');

	//настройка Почты
	define('WT_FROM_EMAIL_MAIL', 'admin@example.com');
	define('WT_FROM_NAME_MAIL', 'admin');
	define('WT_SMTP_MAIL', FALSE);               //включить SMTP
	define('WT_HOST_MAIL', 'smtp.example.com');
	define('WT_AUTH_MAIL', FALSE);               //включить Авторизацию
	define('WT_USERNAME_MAIL', 'admin');
	define('WT_PASSWORD_MAIL', 'admin');
	define('WT_SECURE_MAIL', 'ssl');               //тип шифрования
	define('WT_PORT_MAIL', '465');
	//настройка Локализации
	define('WT_LOCALE_DOMAIN', 'wt');
	define('WT_LOCALE_PATH', WT_BASE_PATH . 'locale' . DIRECTORY_SEPARATOR);
	// Если у вас нет или не работает (как у меня) "gettext", отключите его, здесь будет использована альтернатива
	define('WT_USE_GETTEXT', FALSE);               //extension_loaded('gettext')

	if (!function_exists('WT_LOCALE_SELECT_FUNCTION')) {
		/**
		 * Пользовательская функция возвращающая язык для установки цокали на основе url
		 * вы можете ее менять
		 * @return false|string
		 */
		function WT_LOCALE_SELECT_FUNCTION()
		{
			preg_match('/^(.{1,3})?\..*$/', $_SERVER['HTTP_HOST'], $math);
			if (!isset($math[1])) {
				if (class_exists('\Locale')) {
					return Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
				}
				return 'en';
			}
			return $math[1];
		}
	}
	//настройка системы
	if (!function_exists('getSystem')) {
		function getSystem()
		{
			$sys = strtolower(php_uname('s'));
			if (strpos($sys, 'windows') !== FALSE) {
				return 'win';
			}
			return 'nix';
		}
	}

	if (!function_exists('WT_START_SESSION_FUNCTION')) {
		function WT_START_SESSION_FUNCTION()
		{
			session_set_cookie_params(0, '/');
			session_start();
		}

		function WT_RESTART_SESSION_FUNCTION()
		{
			setcookie('authKey', NULL);
			setcookie('userId', NULL);
			session_unset();
			WT_START_SESSION_FUNCTION();
		}
	}

	define('WT_TYPE_SYSTEM', getSystem());

	Config::set('WT_BASE_PATH', WT_BASE_PATH);
	Config::set('WT_CORE_PATH', WT_CORE_PATH);
	Config::set('WT_CACHE_PATH', WT_CACHE_PATH);
	Config::set('WT_MODEL_PATH', WT_MODEL_PATH);
	Config::set('WT_VENDOR_PATH', WT_VENDOR_PATH);
	Config::set('WT_PAGES_PATH', WT_PAGES_PATH);
	Config::set('WT_VIEWS_PATH', WT_VIEWS_PATH);
	Config::set('WT_CLASSES_PATH', WT_CLASSES_PATH);
	Config::set('WT_TEMPLATES_PATH', WT_TEMPLATES_PATH);
	Config::set('WT_AJAX_PATH', WT_AJAX_PATH);
	Config::set('WT_CRON_PATH', WT_CRON_PATH);
	Config::set('WT_ASSETS_PATH', WT_ASSETS_PATH);
	Config::set('WT_IMAGES_PATH', WT_IMAGES_PATH);
	Config::set('WT_PHP_EXEC_CMD', WT_PHP_EXEC_CMD);
	Config::set('WT_DOMAIN_URL', WT_DOMAIN_URL);
	Config::set('WT_NODE_URL', WT_NODE_URL);
	Config::set('WT_HOST_DB', WT_HOST_DB);
	Config::set('WT_PORT_DB', WT_PORT_DB);
	Config::set('WT_DATABASE_DB', WT_DATABASE_DB);
	Config::set('WT_TYPE_DB', WT_TYPE_DB);
	Config::set('WT_USER_DB', WT_USER_DB);
	Config::set('WT_PASS_DB', WT_PASS_DB);
	Config::set('WT_CHARSET_DB', WT_CHARSET_DB);
	Config::set('WT_HOST_DB', WT_HOST_DB);
	Config::set('WT_PORT_DB', WT_PORT_DB);
	Config::set('WT_DATABASE_DB', WT_DATABASE_DB);
	Config::set('WT_TYPE_DB', WT_TYPE_DB);
	Config::set('WT_USER_DB', WT_USER_DB);
	Config::set('WT_PASS_DB', WT_PASS_DB);
	Config::set('WT_CHARSET_DB', WT_CHARSET_DB);
	Config::set('WT_SMARTY_TEMPLATE_PATH', WT_SMARTY_TEMPLATE_PATH);
	Config::set('WT_SMARTY_COMPILE_PATH', WT_SMARTY_COMPILE_PATH);
	Config::set('WT_SMARTY_CONFIG_PATH', WT_SMARTY_CONFIG_PATH);
	Config::set('WT_SMARTY_CACHE_PATH', WT_SMARTY_CACHE_PATH);
	Config::set('WT_SMARTY_PLUGINS_PATH', WT_SMARTY_PLUGINS_PATH);
	Config::set('WT_FROM_EMAIL_MAIL', WT_FROM_EMAIL_MAIL);
	Config::set('WT_FROM_NAME_MAIL', WT_FROM_NAME_MAIL);
	Config::set('WT_SMTP_MAIL', WT_SMTP_MAIL);
	Config::set('WT_HOST_MAIL', WT_HOST_MAIL);
	Config::set('WT_AUTH_MAIL', WT_AUTH_MAIL);
	Config::set('WT_USERNAME_MAIL', WT_USERNAME_MAIL);
	Config::set('WT_PASSWORD_MAIL', WT_PASSWORD_MAIL);
	Config::set('WT_SECURE_MAIL', WT_SECURE_MAIL);
	Config::set('WT_PORT_MAIL', WT_PORT_MAIL);
	Config::set('WT_LOCALE_DOMAIN', WT_LOCALE_DOMAIN);
	Config::set('WT_LOCALE_PATH', WT_LOCALE_PATH);
	Config::set('WT_USE_GETTEXT', WT_USE_GETTEXT);
	Config::set('WT_TYPE_SYSTEM', WT_TYPE_SYSTEM);

	Config::set('CACHE_PATH', WT_CACHE_PATH);
	Config::set('CACHE_EXPIRATION', 'PDOE', 600);


