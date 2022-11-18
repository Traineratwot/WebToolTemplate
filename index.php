<?php

	namespace index;

	use model\main\Core;
	use Traineratwot\config\Config;

	require_once __DIR__ . '/vendor/autoload.php';
	Core::init();
	require_once Config::get('MODEL_PATH') . 'page/Router.php';
