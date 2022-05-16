<?php

	namespace install;

	use Exception;
	use model\main\Core;
	use RuntimeException;
	use Traineratwot\Cache\Cache;

	error_reporting(0);
	require_once 'vendor/autoload.php';
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
			$i          = json_encode($i);
			$myConfig[] = "if(!defined('$v')){define('$v',$i);}";
		}
	}
	if (!mkdir($concurrentDirectory = WT_CRON_PATH . 'controllers', 0777, 1) && !is_dir($concurrentDirectory)) {
		throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
	}
	if (!mkdir($concurrentDirectory = WT_CRON_PATH . 'locks', 0777, 1) && !is_dir($concurrentDirectory)) {
		throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
	}
	if (!mkdir($concurrentDirectory = WT_CRON_PATH . 'logs', 0777, 1) && !is_dir($concurrentDirectory)) {
		throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
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
		require_once __DIR__.'/vendor/autoload.php';
	Core::init();
		try {
			$core        = Core::init();
			Cache::removeAll();
			$needInstall = !($core->db->tableExists("users"));
			if ($needInstall) {
				$log[] = 'install Database';
				if ($core->db->dsn->getDriver() === 'sqlite') {
					if (!file_exists(WT_HOST_DB)) {
						file_put_contents(WT_HOST_DB, '');
					}
					$core->db->exec(<<<SQL
CREATE TABLE users
(
	id       INTEGER
		PRIMARY KEY AUTOINCREMENT,
	email    VARCHAR(64)
		UNIQUE
			ON CONFLICT FAIL,
	password VARCHAR(64),
	authKey  VARCHAR(64),
	salt     VARCHAR(64) DEFAULT 0
);
SQL
					);
				} else {
					$core->db->exec(<<<SQL
CREATE TABLE `users` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`password` VARCHAR(256) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`authKey` VARCHAR(256) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`salt` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`time_create` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `email` (`email`) USING BTREE,
	UNIQUE INDEX `authKey` (`authKey`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0
;
SQL
					);
				}
			}

		} catch (Exception $e) {
echo '<pre>'; 
var_dump($e); die;

		}
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