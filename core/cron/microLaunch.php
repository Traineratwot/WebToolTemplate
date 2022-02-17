<?php

	use model\Console;

	if (!file_exists(realpath(dirname(__DIR__) . '/config.php'))) {
		echo 'Please check your configuration; ' . __FILE__ . ':7';
		die;
	}
	require_once realpath(dirname(__DIR__) . '/config.php');
	require_once realpath(WT_MODEL_PATH . 'engine.php');
	/** @var Core $core */
	$options = getopt("f:d:");
	$alias     = $options['f'];
	$cron      = realpath(WT_CRON_PATH . 'controllers' . DIRECTORY_SEPARATOR . $alias);
	$key       = md5($alias);
	$lock_path = WT_CRON_PATH . 'locks' . DIRECTORY_SEPARATOR . $key . '.lock';
	if ($cron and file_exists($cron)) {
		include $cron;
	} else {
		die("Could not find file: '" . $cron . "'");
	}