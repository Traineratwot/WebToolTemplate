<?php

	use core\model\make;

	if (PHP_SAPI == 'cli') {
		function note($t)
		{
			$t = ucfirst($t);
			echo '-' . $t . "\n";
		}

		function failure($t)
		{
			$t = ucfirst($t);
			echo "-\033[0;31m" . $t . " \033[0m \n";
		}

		function warning($t)
		{
			$t = ucfirst($t);
			echo "-\033[1;33m" . $t . " \033[0m \n";
		}

		function success($t)
		{
			$t = ucfirst($t);
			echo "-\033[0;32m" . $t . " \033[0m \n";
		}
		try {


			require_once realpath(dirname(__DIR__) . '/config.php');
			require_once realpath(WT_MODEL_PATH . 'engine.php');


			if (empty($argv[1])) {
				failure('empty arguments');
			} else {
				switch (mb_strtolower($argv[1])) {
					case 'make':
						if (!empty($argv[2])) {
							make($argv[2], $argv[3], $argv[4]);
						} else {
							failure('Empty action, eg:wr make {action} {arg}');
						}
						break;
					case 'error':
						if (isset($argv[2]) and $argv[2] == 'clear') {
							unlink(WT_CACHE_PATH . 'error.log');
						} else {
							$f = fopen(WT_CACHE_PATH . 'error.log', 'r');
							$i = 0;
							while (($buffer = fgets($f, 4096)) !== FALSE) {
								$i++;
								$buffer = trim($buffer);
								if (strpos($buffer, '[error]') !== FALSE) {
									failure($i . '. ' . $buffer);
								} elseif (strpos($buffer, '[warning]') !== FALSE) {
									warning($i . '. ' . $buffer);
								} elseif (strpos($buffer, '[info]') !== FALSE) {
									success($i . '. ' . $buffer);
								} else {
									note($i . '. ' . $buffer);
								}
							}
							if (!$i) {
								success('empty logs');
							}
						}
					case 'help':
						note('make {ajax|table|page} {...args}');
						note('error {null|clear}');
						break;
				}
			}

			function make($a, $b, $c)
			{
				switch (mb_strtolower($a)) {
					case 'ajax':
						$class = mb_strtolower(make::name2class($b));
						$p = WT_AJAX_PATH . $class . '.php';
						if (!file_exists($p)) {
							writeFile($p, make::makeAjax($b, $c));
							success('ok: ' . $p);
						} else {
							failure('Already exists');
						}
						break;
					case 'table':
						$class = mb_strtolower(make::name2class($b));
						$p = WT_CLASSES_PATH . $class . '.php';
						if (!file_exists($p)) {
							writeFile($p, make::makeTable($b, $c));
							success('ok: ' . $p);
						} else {
							failure('Already exists');
						}
						break;
					case 'page':
						$url = mb_strtolower($b);
						$p = WT_PAGES_PATH . $url . '.php';
						$p2 = WT_PAGES_PATH . $url . '.tpl';
						if (!file_exists($p)) {
							writeFile($p, make::makePageTpl($url, $c));
							writeFile($p2, make::makePageClass($url, $c));
							success('ok: ' . $p);
						} else {
							failure('Already exists');
						}
						break;
					default:
						failure('Unknown action');
						break;
				}
			}

			function writeFile($path, $content)
			{
				if (!is_dir(dirname($path))) {
					if (!mkdir($concurrentDirectory = dirname($path), 0777, 1) && !is_dir($concurrentDirectory)) {
					} else {
						file_put_contents($path, $content);
					}
				}
			}


		} catch (Exception $e) {
			echo '<pre>';
			print_r($e->getMessage());
			die;
		}
	} else {
		echo '<pre>';
		print_r('Use console');
		die;

	}