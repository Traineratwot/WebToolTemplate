<?php

	namespace index;
	session_start();
	ini_set('display_errors', 1);
	require_once realpath(__DIR__ . '/core/config.php');
	require_once realpath(WT_MODEL_PATH . 'engine.php');
	require_once realpath(WT_MODEL_PATH . 'router.php');
	?>