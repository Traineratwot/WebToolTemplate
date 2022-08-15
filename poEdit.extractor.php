<?php

	namespace index;


	use model\locale\PoUpdate;
	use model\main\Core;

	require_once __DIR__ . '/vendor/autoload.php';
	Core::init();
	(new PoUpdate())->poEdit();