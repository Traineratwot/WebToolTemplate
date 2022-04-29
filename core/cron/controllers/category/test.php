<?php


	use model\main\Core;
	use Traineratwot\PhpCli\Console;

	Console::info('sleep 5');
	$core = Core::init();
	var_dump(Console::getOpt($a));
	var_dump($a);