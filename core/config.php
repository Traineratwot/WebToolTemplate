<?php
	//включаем ошибки
	use Traineratwot\cc\Config;

	ini_set('display_errors', 1);
	error_reporting(E_ERROR);
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
				if (class_exists(Locale::class)) {
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
	Config::set('WT_TYPE_SYSTEM', getSystem(), NULL, FALSE, 'WT_TYPE_SYSTEM');

	//определяем основные пути
	Config::set('WT_BASE_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_BASE_PATH');
	Config::set('WT_CORE_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_CORE_PATH');
	Config::set('WT_CACHE_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_CACHE_PATH');
	Config::set('WT_MODEL_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_MODEL_PATH');
	Config::set('WT_VENDOR_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_VENDOR_PATH');
	Config::set('WT_PAGES_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_PAGES_PATH');
	Config::set('WT_VIEWS_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_VIEWS_PATH');
	Config::set('WT_CLASSES_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_CLASSES_PATH');
	Config::set('WT_TEMPLATES_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_TEMPLATES_PATH');
	Config::set('WT_AJAX_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'ajax' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_AJAX_PATH');
	Config::set('WT_CRON_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_CRON_PATH');
	Config::set('WT_ASSETS_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_ASSETS_PATH');
	Config::set('WT_IMAGES_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_IMAGES_PATH');
	//настройка Cron
	Config::set('WT_PHP_EXEC_CMD', "php", NULL, FALSE, 'WT_PHP_EXEC_CMD'); //команда запуска php скрипта
	//Внешний url
	Config::set('WT_DOMAIN_URL', "https://localhost", NULL, FALSE, 'WT_DOMAIN_URL');
	Config::set('WT_NODE_URL', WT_DOMAIN_URL . '/node_modules' . '/', NULL, FALSE, 'WT_NODE_URL');
	//определяем подключение к своей базе
	Config::set('WT_HOST_DB', WT_CORE_PATH . 'databases/database.db', NULL, FALSE, 'WT_HOST_DB');
	Config::set('WT_PORT_DB', '', NULL, FALSE, 'WT_PORT_DB');
	Config::set('WT_DATABASE_DB', '', NULL, FALSE, 'WT_DATABASE_DB');
	Config::set('WT_TYPE_DB', 'sqlite', NULL, FALSE, 'WT_TYPE_DB');
	Config::set('WT_USER_DB', '', NULL, FALSE, 'WT_USER_DB');
	Config::set('WT_PASS_DB', '', NULL, FALSE, 'WT_PASS_DB');
	Config::set('WT_CHARSET_DB', '', NULL, FALSE, 'WT_CHARSET_DB');
//	Config::set('WT_HOST_DB',    'localhost',null,false,,'WT_HOST_DB');
//	Config::set('WT_PORT_DB',    '3306',null,     false,,'WT_PORT_DB');
//	Config::set('WT_DATABASE_DB','wt',null,       false,,'WT_DATABASE_DB');
//	Config::set('WT_TYPE_DB',    'mysql',null,    false,,'WT_TYPE_DB');
//	Config::set('WT_USER_DB',    'root',null,     false,,'WT_USER_DB');
//	Config::set('WT_PASS_DB',    '',null,         false,,'WT_PASS_DB');
//	Config::set('WT_CHARSET_DB', 'utf8mb4',null,  false,,'WT_CHARSET_DB');
	Config::set('WT_SQL_LOG', TRUE, NULL, FALSE, 'WT_SQL_LOG');
	Config::set('WT_SOCKET_DB', NULL, NULL, FALSE, 'WT_SOCKET_DB');

	//Настройки шаблонизатора
	Config::set('WT_SMARTY_TEMPLATE_PATH', WT_TEMPLATES_PATH, NULL, FALSE, 'WT_SMARTY_TEMPLATE_PATH');
	Config::set('WT_SMARTY_COMPILE_PATH', WT_CACHE_PATH . 'smarty/compile', NULL, FALSE, 'WT_SMARTY_COMPILE_PATH');
	Config::set('WT_SMARTY_CONFIG_PATH', WT_CACHE_PATH . 'smarty/config', NULL, FALSE, 'WT_SMARTY_CONFIG_PATH');
	Config::set('WT_SMARTY_CACHE_PATH', WT_CACHE_PATH . 'smarty/cache', NULL, FALSE, 'WT_SMARTY_CACHE_PATH');
	Config::set('WT_SMARTY_PLUGINS_PATH', WT_CLASSES_PATH . 'smarty' . DIRECTORY_SEPARATOR . 'plugins', NULL, FALSE, 'WT_SMARTY_PLUGINS_PATH');

	//настройка Почты
	Config::set('WT_FROM_EMAIL_MAIL', 'admin@example.com', NULL, FALSE, 'WT_FROM_EMAIL_MAIL');
	Config::set('WT_FROM_NAME_MAIL', 'admin', NULL, FALSE, 'WT_FROM_NAME_MAIL');
	Config::set('WT_SMTP_MAIL', FALSE, NULL, FALSE, 'WT_SMTP_MAIL');               //включить SMTP
	Config::set('WT_HOST_MAIL', 'smtp.example.com', NULL, FALSE, 'WT_HOST_MAIL');
	Config::set('WT_AUTH_MAIL', FALSE, NULL, FALSE, 'WT_AUTH_MAIL');               //включить Авторизацию
	Config::set('WT_USERNAME_MAIL', 'admin', NULL, FALSE, 'WT_USERNAME_MAIL');
	Config::set('WT_PASSWORD_MAIL', 'admin', NULL, FALSE, 'WT_PASSWORD_MAIL');
	Config::set('WT_SECURE_MAIL', 'ssl', NULL, FALSE, 'WT_SECURE_MAIL');               //тип шифрования
	Config::set('WT_PORT_MAIL', '465', NULL, FALSE, 'WT_PORT_MAIL');

	//настройка Локализации
	Config::set('WT_LOCALE_DOMAIN', 'wt', NULL, FALSE, 'WT_LOCALE_DOMAIN');
	Config::set('WT_LOCALE_PATH', WT_BASE_PATH . 'locale' . DIRECTORY_SEPARATOR, NULL, FALSE, 'WT_LOCALE_PATH');
	// Если у вас нет или не работает (как у меня) "gettext", отключите его, здесь будет использована альтернатива
	Config::set('WT_USE_GETTEXT', FALSE, NULL, FALSE, 'WT_USE_GETTEXT');               //extension_loaded('gettext')

	Config::set('CACHE_PATH', WT_CACHE_PATH, NULL, FALSE, 'CACHE_PATH');
	Config::set('CACHE_EXPIRATION', 600, 'PDOE', FALSE, 'CACHE_EXPIRATION');




