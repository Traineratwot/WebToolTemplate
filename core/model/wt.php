<?php
	require_once realpath(dirname(__DIR__) . '/config.php');
	require_once realpath(__DIR__ . '/engine.php');


	if (!empty($argv)) {
		note('empty arguments');
		failure('empty arguments');
		success('empty arguments');
	}
	function note($t)
	{
		echo $t . "\033[0m\n";
	}
	function failure($t)
	{
		if(WT_TYPE_SYSTEM == 'nix') {
			echo "\0330;31".$t . "\033[0m\n";
		}else{
			note($t);
		}
	}
	function success($t)
	{
		if(WT_TYPE_SYSTEM == 'nix') {
			echo "\0330;32".$t . "\n";
		}else{
			note($t);
		}
	}