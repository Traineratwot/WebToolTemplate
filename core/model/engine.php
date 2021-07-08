<?php

	use core\model\Core;

	require_once realpath(WT_MODEL_PATH . 'core.php');
	require_once realpath(WT_MODEL_PATH . 'errors.php');
	require_once realpath(WT_VENDOR_PATH . 'autoload.php');
	if (WT_TYPE_DB == 'sqlite') {
		error_reporting(E_ALL ^ E_WARNING);
	}
	$classes = scandir(WT_CLASSES_PATH);
	foreach ($classes as $class) {
		$p = realpath(WT_CLASSES_PATH . $class);
		if (file_exists($p) and is_file($p)) {
			require_once $p;
		}
	}
	/** @var Core $core */
	$core = new Core();