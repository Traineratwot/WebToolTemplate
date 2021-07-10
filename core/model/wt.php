<?php
	require_once realpath(dirname(__DIR__) . '/config.php');
	require_once realpath(__DIR__ . '/engine.php');
	if (empty($argv[1])) {
		failure('empty arguments');
	} else {
		switch ($argv[1]) {
			case 'make':

				break;
			case 'error':
				if (isset($argv[2]) and $argv[2] == 'clear') {
					unlink(WT_CACHE_PATH . 'error.log');
				} else {
					$f = fopen(WT_CACHE_PATH . 'error.log', 'r');
					while (($buffer = fgets($f, 4096)) !== FALSE) {
						$buffer = trim($buffer);
						if (strpos($buffer, '[error]') !== FALSE) {
							failure($buffer);
						} elseif (strpos($buffer, '[warning]') !== FALSE) {
							warning($buffer);
						}elseif (strpos($buffer, '[info]') !== FALSE) {
							success($buffer);
						}  else {
							note($buffer);
						}
					}

				}
		}
	}
	
	function note($t)
	{
		echo '-' . $t . "\n";
	}

	function failure($t)
	{
		if (WT_TYPE_SYSTEM == 'nix') {
			echo "-\033[0;31m" . $t . " \033[0m \n";
		} else {
			note($t);
		}
	}

	function warning($t)
	{
		if (WT_TYPE_SYSTEM == 'nix') {
			echo "-\033[1;33m" . $t . " \033[0m \n";
		} else {
			note($t);
		}
	}

	function success($t)
	{
		if (WT_TYPE_SYSTEM == 'nix') {
			echo "-\033[0;32m" . $t . " \033[0m \n";
		} else {
			note($t);
		}
	}