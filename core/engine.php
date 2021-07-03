<?php

	namespace core;

	use core\modules\Core;

	require_once realpath(__DIR__ . '/config.php');
	require_once realpath(CORE_PATH . 'errors.php');
	require_once realpath(CORE_PATH . 'core.php');
	require_once realpath(VENDOR_PATH . 'autoload.php');
	$classes = scandir(CLASSES_PATH);
	foreach ($classes as $class) {
		$p = realpath(CLASSES_PATH . $class);
		if (file_exists($p) and is_file($p)) {
			require_once $p;
		}
	}
	$core = new Core();