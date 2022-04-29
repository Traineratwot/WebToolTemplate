<?php

	namespace index;


	use model\locale\PoUpdate;

	require_once __DIR__ . '/core/config.php';
	require_once WT_MODEL_PATH . 'engine.php';
	(new PoUpdate())->poEdit();