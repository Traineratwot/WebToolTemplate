<?php

	namespace core\model;
	//определяем основные пути
	define('WT_BASE_PATH', realpath(dirname(__DIR__)) . '/');
	define('WT_CORE_PATH', realpath(__DIR__) . '/');
	define('WT_CACHE_PATH', WT_CORE_PATH . 'cache/');
	define('WT_MODEL_PATH', WT_CORE_PATH . 'model/');
	define('WT_VENDOR_PATH', WT_CORE_PATH . 'vendor/');
	define('WT_PAGES_PATH', WT_CORE_PATH . 'pages/');
	define('WT_VIEW_PATH', WT_CORE_PATH . 'view/');
	define('WT_CLASSES_PATH', WT_CORE_PATH . 'classes/');
	define('WT_AJAX_PATH', WT_CORE_PATH . 'ajax/');
	define('WT_ASSETS_PATH', WT_BASE_PATH . 'assets/');
	define('WT_IMAGES_PATH', WT_ASSETS_PATH . 'images/');
	//ВНЕШНИЙ URL
	define('WT_DOMAIN_URL', $_SERVER['SERVER_NAME'] ?: $_SERVER['HTTP_HOST']);
	define('WT_NODE_URL', WT_DOMAIN_URL . '/node_modules/');
	//определяем подключение к своей базе
	define('WT_HOST_DB', WT_CORE_PATH . 'databases/database.db');
	define('WT_PORT_DB', '');
	define('WT_DATABASE_DB', '');
	define('WT_TYPE_DB', 'sqlite');
	define('WT_USER_DB', '');
	define('WT_PASS_DB', '');
	define('WT_DSN_DB', WT_TYPE_DB . ":" . WT_HOST_DB);
	//Настройки шаблонизатора
	define('WT_SMARTY_TEMPLATE_PATH', WT_CORE_PATH . 'templates');
	define('WT_SMARTY_COMPILE_PATH', WT_CACHE_PATH . 'smarty/compile');
	define('WT_SMARTY_CONFIG_PATH', WT_CACHE_PATH . 'smarty/config');
	define('WT_SMARTY_CACHE_PATH', WT_CACHE_PATH . 'smarty/cache');