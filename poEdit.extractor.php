<?php

	namespace index;

	use model\Console;
	use model\PoUpdate;

	require_once realpath(__DIR__ . '/core/config.php');
	require_once realpath(WT_MODEL_PATH . 'engine.php');
	Console::prompt('TEST');

	file_put_contents(__DIR__.'/log.log',json_encode($argv));

	(new PoUpdate())->poEdit();