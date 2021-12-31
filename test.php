<?php
	namespace index;
	use core\model\Core;
	require_once realpath(__DIR__ . '/core/config.php');
	require_once realpath(WT_MODEL_PATH . 'engine.php');
	/** @var Core $core */
	header('Content-Type: text/html; charset=utf-8');
	setlocale(LC_ALL, 'ru.UTF-8');
	echo strftime("%A %e %B %Y", mktime(0, 0, 0, 12, 22, 1978));
	?>