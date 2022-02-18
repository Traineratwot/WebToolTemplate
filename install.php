<?php
	error_reporting(0);
	require_once realpath(__DIR__ . '/core/config.php');
	$log = [];
	function installLinux()
	{

		exec('chmod 755 -R -f ' . WT_MODEL_PATH);
		exec('chmod 744 -R -f ' . WT_CORE_PATH . 'config.php');
		exec('chmod 744 -R -f ' . WT_CORE_PATH . 'config.json');
		exec('chmod 744 -R -f ' . WT_AJAX_PATH);
		$GLOBALS['log'][] = 'Permissions set';
		$c                = 'alias wt="php ' . WT_MODEL_PATH . 'wt.php"';
		exec($c);
		$GLOBALS['log'][] = 'command to install wt "' . $c . '"';
	}

	function installWindows()
	{
//		global $log;
	}

	$config   = get_defined_constants();
	$myConfig = [];
	foreach ($config as $v => $i) {
		if (stripos($v, 'WT_') === 0) {
			$i = json_encode($i);
			$myConfig[] = "if(!defined('$v')){define('$v',$i);}";
		}
	}
	if (!mkdir($concurrentDirectory = WT_CRON_PATH . 'controllers', 0777) && !is_dir($concurrentDirectory)) {
		throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
	}
	if (!mkdir($concurrentDirectory = WT_CRON_PATH . 'locks', 0777) && !is_dir($concurrentDirectory)) {
		throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
	}
	if (!mkdir($concurrentDirectory = WT_CRON_PATH . 'logs', 0777) && !is_dir($concurrentDirectory)) {
		throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
	}
	$log[] = 'Folders created';
//	file_put_contents(WT_CORE_PATH . '__config.php', "<?php\n# file for IDE highlight. not use this file \n".implode("\n", $myConfig));
	if (!file_exists(WT_BASE_PATH . 'node_modules')) {
		exec('npm update');
	}
	if (!file_exists(WT_BASE_PATH . 'node_modules')) {
		$log[] = "Failed to install npm\n";
	} else {
		$log[] = 'npm updated';
	}
	if (!file_exists(WT_VENDOR_PATH . 'autoload.php')) {
		exec('composer update');
	}
	if (!file_exists(WT_VENDOR_PATH . 'autoload.php')) {
		$log[] = "Failed to install composer\n";
	} else {
		$log[] = 'composer updated';
		require_once realpath(WT_MODEL_PATH . 'engine.php');
	}
	//----------------------------------------------
	$system = PHP_OS;

	if (WT_TYPE_SYSTEM === 'nix') {
		installLinux();
		exec('clear');
	} else {
		installWindows();
		exec('cls');
	}

	foreach ($log as $key => $value) {
		$log[$key] = ucfirst(trim($value));
	}
	$log_ = implode("\n| ", $log);
	$txt  = <<<TXT
-----------------------------------------------------------
| System: $system
|
| $log_
-----------------------------------------------------------

TXT;
	echo $txt;
	if (empty($argv) || $argv[1] !== 'dev') {
		rename(__FILE__, __FILE__ . '.txt');
	}
	//-----------------------------------------------------