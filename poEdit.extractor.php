<?php

	namespace index;


	use model\locale\PoUpdate;

	require_once realpath(__DIR__ . '/core/config.php');
	require_once realpath(WT_MODEL_PATH . 'engine.php');

	file_put_contents(__DIR__ . '/log.log', json_encode($argv));

	(new PoUpdate())->poEdit();