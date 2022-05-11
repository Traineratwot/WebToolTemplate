<?php


	use model\main\Core;
	use model\main\Utilities;
	use Traineratwot\PhpCli\Console;

	if (!file_exists(realpath(dirname(__DIR__) . '/config.php'))) {
		echo 'Please check your configuration; ' . __FILE__ . ':7';
		die;
	}
	require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
	Core::init();
	$core    = Core::init();
	$options = getopt("f:d:");
	$alias   = $options['f'];

	ob_start();
	$key       = md5($alias);
	$lock_path = WT_CRON_PATH . 'locks' . DIRECTORY_SEPARATOR . $key . '.lock';
	//проверка запущен ли еще предыдущий крон
	if (file_exists($lock_path)) {
		if (!isset($options['d']) || $options['d'] !== "true") {
			Console::failure('Already launched');
			exit();
		}
	}
	//создание lock файла
	try {
		if (!mkdir($concurrentDirectory = dirname($lock_path), 0777, TRUE) && !is_dir($concurrentDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		file_put_contents($lock_path, time());
	} catch (Exception $e) {
	}
	//запуск крона как дочерний процесс что-бы избежать любых остановок/ошибок во время выполнения
	//таким образом что-бы ни произошло, лог будет записан, а блокирующий файл удален
	$start = microtime(TRUE);

	$a    = [];
	$args = array_slice($argv, 2);
	foreach ($args as $val) {
		$a[] = escapeshellcmd($val);
	}
	system(WT_PHP_EXEC_CMD . " " . WT_CRON_PATH . "microLaunch.php -f\"$alias\" " . implode(" ", $a), $output);
	$end = microtime(TRUE);
	//Вывод статистики
	echo PHP_EOL . '------STATS------' . PHP_EOL;
	echo 'Time:            ' . round(abs($end - $start), 3) . ' ms' . PHP_EOL;
	echo 'SQL Queries:     ' . ($core->db->queryCount() ?: (int)$output) . PHP_EOL;
	echo 'Memory used:     ' . Utilities::convertBytes(memory_get_usage()) . PHP_EOL;
	echo 'Memory max used: ' . Utilities::convertBytes(memory_get_peak_usage()) . PHP_EOL;
	echo 'Date:            ' . date('Y-m-d H:i:s');
	//создание log файла
	try {
		$log = WT_CRON_PATH . 'logs' . DIRECTORY_SEPARATOR . $alias . '.log';
		if (!mkdir($concurrentDirectory = dirname($log), 0777, TRUE) && !is_dir($concurrentDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		file_put_contents($log, ob_get_contents());
	} catch (Exception $e) {
	}
	unlink($lock_path);
	ob_end_flush();
