<?php

	namespace index;
	ini_set('display_errors', 1);
	error_reporting(E_ALL);

	use model\main\Core;

	session_start();
	require_once __DIR__ . '/vendor/autoload.php';
	Core::init();
	require_once realpath(WT_MODEL_PATH . 'page/Router.php');
