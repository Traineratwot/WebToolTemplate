#!/usr/bin/env php
<?php

	use model\Console;
	use model\Core;
	use model\make;
	use model\PoUpdate;

	/** @var Console $console */
	/** @var Core $core */
	if (PHP_SAPI == 'cli') {
		function note($t)
		{
			$t = ucfirst($t);
			echo '-' . $t . "\n";
		}

		function make($a, $b, $c)
		{
			switch (mb_strtolower($a)) {
				case 'ajax':
					$p = strtr(WT_AJAX_PATH . $b . '.php', [
							'/'  => DIRECTORY_SEPARATOR,
							'\\' => DIRECTORY_SEPARATOR,
					]);
					$p = mb_strtolower($p);
					if (!file_exists($p)) {
						writeFile($p, make::makeAjax($b, $c));
						Console::success('ok: ' . $p);
					} else {
						Console::failure('Already exists');
					}
					break;
				case 'table':
					$class = make::name2class($b);
					$p     = WT_CLASSES_PATH . 'tables/' . $class . '.php';
					$p     = mb_strtolower($p);
					global $core;
					if (!file_exists($p)) {
						writeFile($p, make::makeTable($b, $c));
						if (file_exists($p)) {
							include $p;
							$core->getObject($class);
							Console::success('ok: ' . $p);
						} else {
							Console::failure('can`t write: ' . $p);
						}
					} else {
						include $p;
						$core->getObject($class);
						Console::warning('Already exists');
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
					$p   = mb_strtolower($p);
					$p2  = mb_strtolower($p2);
					if (!file_exists($p)) {
						if (writeFile($p, make::makePageClass($url, $c))) {
							Console::success('ok: ' . $p);
						} else {
							Console::failure('can`t write file: ' . $p);
						}
					} else {
						Console::failure('Already exists: ' . $p);
					}
					if (!file_exists($p2)) {
						if (writeFile($p2, make::makePageTpl($url, $c))) {
							Console::success('ok: ' . $p2);
						} else {
							Console::failure('can`t write file: ' . $p);
						}
					} else {
						Console::failure('Already exists: ' . $p2);
					}
					break;
				default:
					Console::failure('Unknown action');
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

		function localeGenerator($lang)
		{
			require_once(WT_MODEL_PATH . 'poUpdate.php');
			(new PoUpdate())->run($lang);
			Console::success('ok');
			exit();
		}

		try {
			require_once realpath(dirname(__DIR__) . '/config.php');
			require_once realpath(WT_MODEL_PATH . 'engine.php');
			if (empty($argv[1])) {
				Console::failure('empty arguments');
			} else {
				switch (mb_strtolower($argv[1])) {
					case 'make':
						if (!empty($argv[2])) {
							make($argv[2], $argv[3], $argv[4]);
						} else {
							Console::failure('Empty action, eg:wr make {action} {arg}');
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
									Console::failure($i . '. ' . $buffer);
								} elseif (strpos($buffer, '[warning]') !== FALSE) {
									Console::warning($i . '. ' . $buffer);
								} elseif (strpos($buffer, '[info]') !== FALSE) {
									Console::success($i . '. ' . $buffer);
								} else {
									note($i . '. ' . $buffer);
								}
							}
							if (!$i) {
								Console::success('empty logs');
							}
						}
						break;
					case 'cache':
						try {
							if (WT_TYPE_SYSTEM == 'win') {
								system('RD /s/q "' . WT_CACHE_PATH . '"');
							} else {
								if ($argv[3] == 'sudo' or $argv[2] == 'sudo') {
									system('sudo rm -rf "' . WT_CACHE_PATH . '"');
								} else {
									system('rm -rf "' . WT_CACHE_PATH . '"');
								}
							}

							if (!file_exists(WT_CACHE_PATH)) {
								Console::success('cache cleaned');
							} else {
								Console::failure('error');
							}

						} catch (\Exception $e) {
							Console::failure($e->getMessage());
						}
						break;
					case 'help':
						Console::success('make {ajax|table|page} {...args}; generate template for ajax, table class, or page');
						Console::success('error {?clear}; --- get error log or clear');
						Console::success('cache {clear}; --- exterminate cache folder');
						Console::success('lang {lang code} e.g.: lang ru_RU.utf8; --- generate .po and .mo files in locale folder');
						break;
					case 'lang':
					case 'locale':
						$lang = $argv[2];
						if ($lang == 'all') {
							if (WT_TYPE_SYSTEM === 'nix') {
								exec("locale -a", $out);
								Console::success('Installed locale:');
								print_r($out);
							} else {
								Console::failure("windows don`t have command to get locale, use template 'XX.utf8' where XX - lang code");
							}
						} elseif ($lang == 'clear') {
							$dir = WT_CACHE_PATH . 'locale' . DIRECTORY_SEPARATOR;
							if (is_dir($dir)) {
								if (WT_TYPE_SYSTEM == 'win') {
									system('RD /s/q "' . $dir . '"');
								} else {
									if ($argv[3] == 'sudo' or $argv[2] == 'sudo') {
										system('sudo rm -rf "' . $dir . '"');
									} else {
										system('rm -rf "' . $dir . '"');
									}
								}
							}
							chdir(WT_LOCALE_PATH);
							foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.mo', GLOB_BRACE) as $_file) {
								unlink($_file);
							}
							foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.json', GLOB_BRACE) as $_file) {
								unlink($_file);
							}
							foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.php', GLOB_BRACE) as $_file) {
								unlink($_file);
							}
							Console::success('ok');
						} elseif ($lang) {
							if (WT_TYPE_SYSTEM === 'nix') {
								exec("locale -a|grep {$lang}", $out);
								if (empty($out)) {
									Console::failure("please install locales");
									break;
								}
								$t = FALSE;
								foreach ($out as $locale) {
									if ($locale == $lang) {
										$t = TRUE;
										break;
									}
								}
								if (!$t) {
									print_r($out);
									Console::failure("Chose lang from that list");
									break;
								} else {
									Console::success('start generator');
									localeGenerator($lang);
								}
							} else {
								$newLang = Core::setLocale($lang, FALSE);
								if (stripos($lang, 'utf8') === FALSE) {
									Console::warning("Recommend add '.utf8");
									if (!(int)Console::prompt('Continue with? "' . $lang . '" 1/0')) {
										break;
									};
								}
								if ($newLang == FALSE) {
									Console::failure("can't set locale '$lang' ");
								} elseif ($lang == $newLang) {
									Console::success('start generator');
									localeGenerator($newLang);
								} else {
									Console::warning("can't set locale '$lang' but set '$newLang' ");
									if (!(int)Console::prompt('Continue with "' . $newLang . '" ? 1/0')) {
										break;
									};
									Console::success('start generator');
									localeGenerator($newLang);
								}
							}
						} else {
							Console::failure("haven`t 1 argument");
						}
						break;
					case 'cron':
						$alias = $argv[2];
						if (!$alias) {
							Console::failure('Missing path to cron controller');
						} else {
							$alias = strtr($alias, [
									'/'  => DIRECTORY_SEPARATOR,
									'\\' => DIRECTORY_SEPARATOR,
							]);
							$cron  = realpath(WT_CRON_PATH . 'controllers' . DIRECTORY_SEPARATOR . $alias);
							if ($cron and file_exists($cron)) {
								$cmd = ' php ' . WT_CRON_PATH . 'launch.php -f"' . $alias . '"';
								if ($argv[4] == 'dev') {
									$cmd .= ' -d true';
								}
								if ($argv[3] == 'run') {
									exec($cmd, $out);
									echo implode("\n", $out) . PHP_EOL;
								} else {
									Console::success($cmd);
								}
							} else {
								Console::failure('Wrong path');
							}
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