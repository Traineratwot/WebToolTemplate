<?php

	namespace index;
	session_start();
	require_once realpath(__DIR__ . '/core/__config.php');
	require_once realpath(__DIR__ . '/core/config.php');
	require_once realpath(WT_MODEL_PATH . 'engine.php');
	require_once realpath(WT_MODEL_PATH . 'page/router.php');
	?>