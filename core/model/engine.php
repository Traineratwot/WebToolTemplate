<?php

	namespace core\model;
	require_once realpath(WT_MODEL_PATH . 'core.php');
	require_once realpath(WT_MODEL_PATH . 'postFiles.php');
	require_once realpath(WT_MODEL_PATH . 'errors.php');
	require_once realpath(WT_VENDOR_PATH . 'autoload.php');
	$classes = util::glob(WT_CLASSES_PATH, '*.php');
	foreach ($classes as $class) {
		if (file_exists($class) and is_file($class)) {
			include_once $class;
		}
	}
	/** @var Core $core */
	$core = new Core();