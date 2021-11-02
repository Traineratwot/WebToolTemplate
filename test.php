<?php

	namespace index;

	use core\model\Core;

	require_once realpath(__DIR__ . '/core/config.php');
	require_once realpath(WT_MODEL_PATH . 'engine.php');

	/** @var Core $core */
//	$core->cache->setCache('dffsdfsd',$_SERVER,5);
	$a = $core->cache->getCache('dffsdfsd', $_SERVER, 5);
	echo '<pre>';
	var_dump($a);
	die;

	?>