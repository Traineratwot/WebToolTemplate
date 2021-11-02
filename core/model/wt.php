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

		function make($a, $b, $c)
		{
			switch (mb_strtolower($a)) {
				case 'ajax':
					$class = mb_strtolower(make::name2class($b));
					$p     = WT_AJAX_PATH . $class . '.php';
					if (!file_exists($p)) {
						writeFile($p, make::makeAjax($b, $c));
						success('ok: ' . $p);
					} else {
						failure('Already exists');
					}
					break;
				case 'table':
					$class = mb_strtolower(make::name2class($b));
					$p     = WT_CLASSES_PATH . $class . '.php';
					if (!file_exists($p)) {
						writeFile($p, make::makeTable($b, $c));
						success('ok: ' . $p);
					} else {
						failure('Already exists');
					}
					break;
				case 'page':
					$url = mb_strtolower($b);
					$p   = strtr(WT_VIEWS_PATH . $url . '.php', [
						'/'  => DIRECTORY_SEPARATOR,
						'\\' => DIRECTORY_SEPARATOR,
					]);
					$p2  = strtr(WT_PAGES_PATH . $url . '.tpl', [
						'/'  => DIRECTORY_SEPARATOR,
						'\\' => DIRECTORY_SEPARATOR,
					]);
					if (!file_exists($p)) {
						if (writeFile($p, make::makePageClass($url, $c))) {
							success('ok: ' . $p);
						} else {
							failure('can`t write file: ' . $p);
						}
					} else {
						failure('Already exists: ' . $p);
					}
					if (!file_exists($p2)) {
						if (writeFile($p2, make::makePageTpl($url, $c))) {
							success('ok: ' . $p2);
						} else {
							failure('can`t write file: ' . $p);
						}
					} else {
						failure('Already exists: ' . $p2);
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
				$concurrentDirectory = dirname($path);
				if (!file_exists($concurrentDirectory) or !is_dir($concurrentDirectory)) {
					if (!mkdir($concurrentDirectory, 0777, 1) && !is_dir($concurrentDirectory)) {
						throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
					}
				}

			}
			file_put_contents($path, $content);
			return file_exists($path);
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
						break;
					case 'cache':
						try {
							if (WT_TYPE_SYSTEM == 'win') {
								system('RD /s/q "' . WT_CACHE_PATH . '"');
							} else {
								system('rm -rf "' . WT_CACHE_PATH . '"');
							}

							if (!file_exists(WT_CACHE_PATH)) {
								success('cache cleaned');
							} else {
								failure('error');
							}

						} catch (\Exception $e) {
							failure($e->getMessage());
						}
						break;
					case 'help':
						note('make {ajax|table|page} {...args}');
						note('error {?clear}');
						note('cache {clear}');
						break;
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