<?php

	namespace index;

	use model\main\Core;

	session_start();
	require_once __DIR__ . '/vendor/autoload.php';
	Core::init();
	require_once WT_MODEL_PATH . 'page/Router.php';
