<?php

	namespace core\model;

	require_once realpath(dirname(__DIR__) . '/config.php');
	require_once realpath(__DIR__ . '/engine.php');
	if (empty($argv[1])) {
		failure('empty arguments');
	} else {
		switch (mb_strtolower($argv[1])) {
			case 'make':
				switch (mb_strtolower($argv[2])) {
					case 'ajax':
						$p = WT_AJAX_PATH . $argv[3] . '.php';
						if (!file_exists($p)) {
							file_put_contents($p, make::makeAjax($argv[3], $argv[4]));
							success('ok: ' . $p);
						} else {
							failure('Already exists');
						}
						break;
					case 'table':
						$class = make::table2class($argv[3]);
						$p = WT_CLASSES_PATH . $class . '.php';
						if (!file_exists($p)) {
							file_put_contents($p, make::makeTable($argv[3], $argv[4]));
							success('ok: ' . $p);
						} else {
							failure('Already exists');
						}
						break;
				}
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
						} elseif (strpos($buffer, '[info]') !== FALSE) {
							success($buffer);
						} else {
							note($buffer);
						}
					}
					if (!$buffer) {
						success('empty logs');
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
		echo "-\033[0;31m" . $t . " \033[0m \n";
	}

	function warning($t)
	{
		echo "-\033[1;33m" . $t . " \033[0m \n";
	}

	function success($t)
	{
		echo "-\033[0;32m" . $t . " \033[0m \n";
	}