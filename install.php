<?php
	error_reporting(0);
	require_once realpath(__DIR__ . '/core/config.php');
	$config = get_defined_constants();
	foreach ($config as $v => $i) {
		if (stripos($v, 'WT_') === 0) {
			$a = explode('_', $v);
			$myConfig[$a[count($a) - 1]][$v] = [
				'type' => $a[count($a) - 1],
				'value' => $i,
				'name' => $v,
			];
			if (stripos($v, '_PATH') !== FALSE) {
				if (!file_exists($i) or !is_dir($i)) {
					if (!mkdir($i, 0777, 1) && !is_dir($i)) {
						throw new \RuntimeException(sprintf('Directory "%s" was not created', $v));
					}
				}
			}
		}
	}
	$log[] = 'Folders created';
	file_put_contents(WT_CORE_PATH . 'config.json', json_encode($myConfig));
	if (!file_exists(WT_BASE_PATH . 'node_modules')) {
		exec('npm update');
	}
	if (!file_exists(WT_BASE_PATH . 'node_modules')) {
		$log[] = "Failed to install npm\n";
	}else{
		$log[] = 'npm updated';
	}
	if (!file_exists(WT_VENDOR_PATH . 'autoload.php')) {
		exec('cd ' . WT_CORE_PATH . ' && composer update');
	}
	if (!file_exists(WT_VENDOR_PATH . 'autoload.php')) {
		$log[] = "Failed to install composer\n";
	} else {
		$log[] = 'composer updated';
		require_once realpath(WT_MODEL_PATH . 'engine.php');
	}
	//----------------------------------------------
	$system = php_uname('s');

	if (WT_TYPE_SYSTEM == 'nix') {
		installLinux();
		exec('clear');
	} else {
		installWindows();
		exec('cls');
	}

	foreach($log as $key => $value){
		$log[$key] = ucfirst(trim($value));
	}
	$log = implode("\n| ", $log);
	$txt = <<<TXT
-----------------------------------------------------------
| System: $system
|
| $log
-----------------------------------------------------------

TXT;
	echo $txt;
	if (empty($argv) or $argv[1] != 'dev') {
		rename(__FILE__, __FILE__ . '.txt');
	}
	//-----------------------------------------------------
	function installLinux()
	{
		global $log;
		exec('chmod 755 -R -f ' . WT_MODEL_PATH);
		exec('chmod 744 -R -f ' . WT_CORE_PATH . 'config.php');
		exec('chmod 744 -R -f ' . WT_CORE_PATH . 'config.json');
		exec('chmod 744 -R -f ' . WT_AJAX_PATH);
		$log[] = 'Permissions set';
		exec('alias make="php ' . WT_MODEL_PATH . 'make.php"');
		$log[] = 'command "wt" installed';
	}

	function installWindows()
	{
		global $log;
	}