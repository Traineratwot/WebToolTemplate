<?php

	namespace model\cli\commands;

	use model\cli\types\CronFilePath;
	use model\main\Utilities;
	use TiBeN\CrontabManager\CrontabAdapter;
	use TiBeN\CrontabManager\CrontabJob;
	use TiBeN\CrontabManager\CrontabRepository;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\types\TBool;
	use Traineratwot\PhpCli\types\TString;

	class CronCmd extends Cmd
	{
		public CrontabRepository $crontabRepository;
		public CrontabAdapter    $crontabAdapter;

		public function __construct()
		{
                $this->crontabAdapter = new CrontabAdapter();
                $this->crontabRepository = new CrontabRepository($this->crontabAdapter);
		}

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "Управление крон задачами";
		}

		public function run()
		{
			$path = $this->getArg('path');
			$cron = $this->getArg('cron');
			if (is_null($path)) {
				$array_cron = Utilities::glob(Config::get('CRON_PATH') . 'controllers/', '*.*');
				$array_cron = array_unique($array_cron);
				Console::success('Список кронов');
				$s = Utilities::pathNormalize(Config::get('CRON_PATH') . 'controllers/', '/');
				foreach ($array_cron as $cron) {
					$cron = Utilities::pathNormalize($cron, '/');
					$find = $this->findInCronTab($cron);
					if ($find === FALSE) {
						Console::info('    ' . str_replace($s, '', $cron));
					} elseif ($find === 'error') {
						Console::error('    ' . str_replace($s, '', $cron));
					} elseif($find->enabled) {
						Console::success('    ' . str_replace($s, '', $cron));
					}else{
						Console::warning('    ' . str_replace($s, '', $cron).' --disabled');
					}
				}
			} else {
				$cmd     = $this->getArg('cmd') ?: Config::get('PHP_EXEC_CMD', NULL, 'php');
				$run     = $this->getArg('run');
				$command = $cmd . ' ' . Config::get('CRON_PATH') . 'launch.php -f"' . $path . '"';
				if (!is_null($run)) {
					if ($run === TRUE) {
						$command .= ' -d true';
					}
					exec($command, $out);
					echo implode("\n", $out) . PHP_EOL;
				} else {
					echo Console::getColoredString($command, 'green') . PHP_EOL;
				}
				if (!is_null($cron)) {
					if (Config::get('TYPE_SYSTEM') === 'win') {
						Console::error('Невозможно добавить задачу на windows');
						return;
					}
					$enable     = $this->getArg('disable');
					$enable     = is_null($enable);
					$minutes    = $this->getArg('minutes') ?: '*';
					$hours      = $this->getArg('hours') ?: '*';
					$day        = $this->getArg('day') ?: '*';
					$months     = $this->getArg('months') ?: '*';
					$week       = $this->getArg('week') ?: '*';
					$crn        = implode(' ', [
						$minutes,
						$hours,
						$day,
						$months,
						$week,
					]);
					$crontabJob = new CrontabJob();
					$key        = md5($command);
					$results    = $this->crontabRepository->findJobByRegex('@' . preg_quote($key, '@') . '@');
					foreach ($results as $j) {
						$this->crontabRepository->removeJob($j);
					}

					$crontabJob
						->setMinutes($minutes)
						->setHours($hours)
						->setDayOfMonth($day)
						->setMonths($months)
						->setDayOfWeek($week)
						->setTaskCommandLine($command)
						->setComments($key)
						->setEnabled($enable)
					;
					$this->crontabRepository->addJob($crontabJob);
					$this->crontabRepository->persist();
					Console::info($crn);
				}
			}
		}

		public function setup():void
		{
			$this->registerParameter('path', 0, CronFilePath::class, "Относительный путь  от папки 'controllers' до файла задания");

			$this->registerOption('cmd', 'c', 0, TString::class, "Введите CMD команду для запуска файла по умолчанию 'Config::get('PHP_EXEC_CMD')' ");
			$this->registerOption('run', 'r', 0, TBool::class, "Установите этот флаг чтобы запустить задание");
			$this->registerOption('cron', 'j', 0, TBool::class, "Добавить задание в кронта. доступно только на серверах с досупом к команде crontab. eg: -j=\"*/5 * * * *\"");

			$this->registerOption('minutes', 'm', 0, TString::class, "minutes. default '*'");
			$this->registerOption('hours', 'h', 0, TString::class, "hours. default '*'");
			$this->registerOption('day', 'd', 0, TString::class, "day. default '*'");
			$this->registerOption('months', 'M', 0, TString::class, "months. default '*'");
			$this->registerOption('week', 'w', 0, TString::class, "week. default '*'");
			$this->registerOption('disable', 'D', 0, TBool::class, "disable cron job");

		}

		private function findInCronTab(string $cron)
		: CrontabJob|bool|string
		{
			$chunk = str_replace(WT_CRON_PATH . 'controllers/', '', $cron);
			$chunk = preg_quote($chunk, '/');
			$list  = $this->crontabRepository?->findJobByRegex("@.+{$chunk}.+@");

			if (!empty($list)) {
				if (count($list) === 1) {
					return $list[0];
				}
				return 'error';
			}
			return FALSE;
		}
	}
