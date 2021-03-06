<?php

	namespace cron;

	use model\main\Core;
	use Traineratwot\config\Config;

	if (!file_exists(realpath(dirname(__DIR__) . '/config.php'))) {
		echo 'Please check your configuration; ' . __FILE__ . ':7';
		die;
	}
	require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
	Core::init();
	$options = getopt("f:d:");
	$alias   = $options['f'];
	$key     = md5($alias);
	$cron    = realpath(Config::get('CRON_PATH') . 'controllers' . DIRECTORY_SEPARATOR . $alias);
	if ($cron && file_exists($cron)) {
		$core = Core::init();
		ob_start();
		include $cron;
		ob_end_flush();
		exit($core->db->queryCount());
	}
	die("Could not find file: '" . $cron . "'");