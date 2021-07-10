<?php
	require_once realpath(dirname(__DIR__) . '/config.php');
	require_once realpath(__DIR__ . '/engine.php');


	if (!empty($argv)) {
		failure('empty arguments');
	}

	
	function note($t)
	{
		echo " ".$t . "\n";
	}
	function failure($t)
	{
		if (WT_TYPE_SYSTEM == 'nix') {
			echo "\033[0;31m " . $t . " \033[0m\n";
		} else {
			note($t);
		}
	}
	function success($t)
	{
		if (WT_TYPE_SYSTEM == 'nix') {
			echo "\033[0;32m " . $t . " \033[0m\n";
		} else {
			note($t);
		}
	}