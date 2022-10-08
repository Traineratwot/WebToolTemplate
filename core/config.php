<?php
	//включаем ошибки
	use Traineratwot\config\Config;

	date_default_timezone_set('Europe/Moscow');

	ini_set('display_errors', 1);
	error_reporting(E_ERROR || E_PARSE);
	if (!function_exists('WT_LOCALE_SELECT_FUNCTION')) {
		/**
		 * Пользовательская функция возвращающая язык для установки цокали на основе url
		 * вы можете ее менять
		 * @return false|string
		 */
		function WT_LOCALE_SELECT_FUNCTION()
		{
			if (class_exists(Locale::class)) {
				return Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
			}
			return $_COOKIE['lang'] ?? 'en';
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
			setcookie('authKey', 'WT');
			setcookie('userId', 'WT');
			session_unset();
			WT_START_SESSION_FUNCTION();
		}
	}
	Config::set('TYPE_SYSTEM', getSystem(), 'WT', FALSE, 'WT_TYPE_SYSTEM');

	//определяем основные пути
	Config::set('BASE_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_BASE_PATH');
	Config::set('CORE_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_CORE_PATH');
	Config::set('PLUGINS_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_PLUGINS_PATH');
	Config::set('CACHE_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_CACHE_PATH');
	Config::set('MODEL_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_MODEL_PATH');
	Config::set('VENDOR_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_VENDOR_PATH');
	Config::set('PAGES_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_PAGES_PATH');
	Config::set('COMPONENTS_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_PAGES_PATH');
	Config::set('VIEWS_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_VIEWS_PATH');
	Config::set('CLASSES_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_CLASSES_PATH');
	Config::set('TEMPLATES_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_TEMPLATES_PATH');
	Config::set('AJAX_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'ajax' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_AJAX_PATH');
	Config::set('CRON_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_CRON_PATH');
	Config::set('ASSETS_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_ASSETS_PATH');
	Config::set('IMAGES_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_IMAGES_PATH');
	Config::set('COMPOSER_EXEC_PATH', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR.'composer.phar', 'WT', FALSE, 'WT_COMPOSER_EXEC_PATH'); //команда запуска php скрипта
	//настройка Cron
	Config::set('PHP_EXEC_CMD', "php", 'WT', FALSE, 'WT_PHP_EXEC_CMD'); //команда запуска php скрипта
	//Внешний url
	Config::set('DOMAIN_URL', "https://localhost", 'WT', FALSE, 'WT_DOMAIN_URL');
	Config::set('NODE_URL', Config::get('DOMAIN_URL') . '/node_modules' . '/', 'WT', FALSE, 'WT_NODE_URL');
//	определяем подключение к своей базе
	Config::set('HOST_DB', Config::get('CORE_PATH') . 'databases/database.db', 'WT', FALSE, 'WT_HOST_DB');
	Config::set('PORT_DB', '', 'WT', FALSE, 'WT_PORT_DB');
	Config::set('DATABASE_DB', '', 'WT', FALSE, 'WT_DATABASE_DB');
	Config::set('TYPE_DB', 'sqlite', 'WT', FALSE, 'WT_TYPE_DB');
	Config::set('USER_DB', '', 'WT', FALSE, 'WT_USER_DB');
	Config::set('PASS_DB', '', 'WT', FALSE, 'WT_PASS_DB');
	Config::set('CHARSET_DB', '', 'WT', FALSE, 'WT_CHARSET_DB');


//	Config::set('HOST_DB', 'localhost', 'WT', FALSE, 'WT_HOST_DB');
//	Config::set('PORT_DB', 3306, 'WT', FALSE, 'WT_PORT_DB');
//	Config::set('DATABASE_DB', 'wt', 'WT', FALSE, 'WT_DATABASE_DB');
//	Config::set('TYPE_DB', 'mysql', 'WT', FALSE, 'WT_TYPE_DB');
//	Config::set('USER_DB', 'user', 'WT', FALSE, 'WT_USER_DB');
//	Config::set('PASS_DB', 'pass', 'WT', FALSE, 'WT_PASS_DB');
//	Config::set('CHARSET_DB', '', 'WT', FALSE, 'WT_CHARSET_DB');

	Config::set('SQL_LOG', TRUE, 'WT', FALSE, 'WT_SQL_LOG');
	Config::set('SOCKET_DB', 'WT', 'WT', FALSE, 'WT_SOCKET_DB');

	//Настройки шаблонизатора
	Config::set('SMARTY_TEMPLATE_PATH', Config::get('TEMPLATES_PATH'), 'WT', FALSE, 'WT_SMARTY_TEMPLATE_PATH');
	Config::set('SMARTY_COMPILE_PATH', Config::get('CACHE_PATH') . 'smarty/compile', 'WT', FALSE, 'WT_SMARTY_COMPILE_PATH');
	Config::set('SMARTY_CONFIG_PATH', Config::get('CACHE_PATH') . 'smarty/config', 'WT', FALSE, 'WT_SMARTY_CONFIG_PATH');
	Config::set('SMARTY_CACHE_PATH', Config::get('CACHE_PATH') . 'smarty/cache', 'WT', FALSE, 'WT_SMARTY_CACHE_PATH');
	Config::set('SMARTY_PLUGINS_PATH', Config::get('CLASSES_PATH') . 'smarty' . DIRECTORY_SEPARATOR . 'plugins', 'WT', FALSE, 'WT_SMARTY_PLUGINS_PATH');

	//настройка Почты
	Config::set('FROM_EMAIL_MAIL', 'admin@example.com', 'WT', FALSE, 'WT_FROM_EMAIL_MAIL');
	Config::set('FROM_NAME_MAIL', 'admin', 'WT', FALSE, 'WT_FROM_NAME_MAIL');
	Config::set('SMTP_MAIL', FALSE, 'WT', FALSE, 'WT_SMTP_MAIL');               //включить SMTP
	Config::set('HOST_MAIL', 'smtp.example.com', 'WT', FALSE, 'WT_HOST_MAIL');
	Config::set('AUTH_MAIL', FALSE, 'WT', FALSE, 'WT_AUTH_MAIL');               //включить Авторизацию
	Config::set('USERNAME_MAIL', 'admin', 'WT', FALSE, 'WT_USERNAME_MAIL');
	Config::set('PASSWORD_MAIL', 'admin', 'WT', FALSE, 'WT_PASSWORD_MAIL');
	Config::set('SECURE_MAIL', 'ssl', 'WT', FALSE, 'WT_SECURE_MAIL');               //тип шифрования
	Config::set('PORT_MAIL', '465', 'WT', FALSE, 'WT_PORT_MAIL');

	//настройка Локализации
	Config::set('LOCALE_DOMAIN', 'wt', 'WT', FALSE, 'WT_LOCALE_DOMAIN');
	Config::set('LOCALE_PATH', Config::get('BASE_PATH') . 'locale' . DIRECTORY_SEPARATOR, 'WT', FALSE, 'WT_LOCALE_PATH');
	// Если у вас нет или не работает (как у меня) "gettext", отключите его, здесь будет использована альтернатива
	Config::set('USE_GETTEXT', FALSE, 'WT', FALSE, 'WT_USE_GETTEXT');               //extension_loaded('gettext')

	Config::set('CACHE_PATH', Config::get('CACHE_PATH'), 'WT', FALSE, 'CACHE_PATH');
	Config::set('CACHE_EXPIRATION', 600, 'PDOE', FALSE, 'CACHE_EXPIRATION');

	//Создние всех путей
	foreach (Config::$aliases as $key => $path) {
		if (!empty($path) && str_contains($key, 'PATH') && !file_exists($path) && !mkdir($path, 0777, TRUE) && !is_dir($path)) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
		}
	}
