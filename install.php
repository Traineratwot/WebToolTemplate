<?php

	use core\model\util;

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
	file_put_contents(WT_CORE_PATH . 'config.json', json_encode($myConfig));
	
	if (!file_exists(WT_BASE_PATH . 'node_modules')) {
		exec('npm update');
	}
	if (!file_exists(WT_BASE_PATH . 'node_modules')) {
		echo "Не удалось установить npm";
	}
	if (!file_exists(WT_VENDOR_PATH . 'autoload.php')) {
		exec('cd '.WT_CORE_PATH );
		exec('composer update');
	}
	if (!file_exists(WT_BASE_PATH . 'node_modules')) {
		echo "Не удалось установить composer";
	}else{
		require_once realpath(WT_MODEL_PATH . 'engine.php');
		if (util::getSystem() == 'nix') {
			exec('chmod 755 -R -f '.WT_MODEL_PATH);
			exec('chmod 744 -R -f '.WT_CORE_PATH.'config.php');
			exec('chmod 744 -R -f '.WT_CORE_PATH.'config.json');
			exec('chmod 766 -R -f '.WT_AJAX_PATH);
		}
	}
	rename(__FILE__,__FILE__.'.txt');