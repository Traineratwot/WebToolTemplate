<?php

	namespace model\cli\commands;

	use model\cli\types\FilePath;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\types\TBool;
	use Traineratwot\PhpCli\types\TString;

	class CronCmd extends Cmd
	{
		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "Управление крон задачами";
		}

		public function run()
		{
			if (!defined('WT_PHP_EXEC_CMD')) {
				define('WT_PHP_EXEC_CMD', 'php');
			}
			$path    = $this->getArg('path');
			$cmd     = $this->getArg('cmd') ?: WT_PHP_EXEC_CMD;
			$run     = $this->getArg('run');
			$command = $cmd . ' ' . WT_CRON_PATH . 'launch.php -f"' . $path . '"';
			if (!is_null($run)) {
				if ($run === TRUE) {
					$command .= ' -d true';
				}
				exec($command, $out);
				echo implode("\n", $out) . PHP_EOL;
			} else {
				echo Console::getColoredString($command, 'green');
			}
		}

		public function setup()
		{
			$this->registerParameter('path', 1, FilePath::class, "Относительный путь  от папки 'controllers' до файла задания");
			$this->registerOption('cmd', 'c', 0, TString::class, "Введите CMD коммаду для запуска файла по умодчанию 'WT_PHP_EXEC_CMD' ");
			$this->registerOption('run', 'r', 0, TBool::class, "Установите этот флаг чтобы запустить задание");
		}
	}