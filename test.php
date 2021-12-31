<?php

	namespace index;

	use core\model\Core;
	use core\model\PoUpdate;

	require_once realpath(__DIR__ . '/core/config.php');
	require_once realpath(WT_MODEL_PATH . 'engine.php');
	/** @var Core $core */
	echo '<pre>';
//	$re  = '/(_\(([\'"].*[\'"])\))/m';
//	$str = '		<h2>{_(\'login\')}</h2>
//';
//
//	preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
//
//// Print the entire match result
//	var_dump($matches);
//	die;
	require_once(WT_MODEL_PATH . 'PoUpdate.php');
	new PoUpdate('ru.UTF-8');
	?>