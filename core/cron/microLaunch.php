<?php

	namespace cron;

	use model\main\Core;

	if (!file_exists(realpath(dirname(__DIR__) . '/config.php'))) {
		echo 'Please check your configuration; ' . __FILE__ . ':7';
		die;
	}
	require_once dirname(__DIR__) . '/config.php';
	require_once WT_MODEL_PATH . 'engine.php';
	$options   = getopt("f:d:");
	$alias     = $options['f'];
	$key       = md5($alias);
	$cron      = realpath(WT_CRON_PATH . 'controllers' . DIRECTORY_SEPARATOR . $alias);
	if ($cron && file_exists($cron)) {
		$core      = Core::init();
		ob_start();
		include $cron;
		ob_end_flush();
		exit($core->db->queryCount());
	}

	die("Could not find file: '" . $cron . "'");