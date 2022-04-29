<?php
	//включаем ошибки
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
	define('WT_DSN_DB', WT_TYPE_DB . ":" . WT_HOST_DB);
//	define('WT_HOST_DB', 'localhost');
//	define('WT_PORT_DB', '3306');
//	define('WT_DATABASE_DB', 'wt');
//	define('WT_TYPE_DB', 'mysql');
//	define('WT_USER_DB', 'root');
//	define('WT_PASS_DB', '');
//	define('WT_CHARSET_DB', 'utf8mb4');
//	define('WT_DSN_DB', WT_TYPE_DB . ":host=" . WT_HOST_DB . ";port=" . WT_PORT_DB . ";dbname=" . WT_DATABASE_DB.";charset=". WT_CHARSET_DB);
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

