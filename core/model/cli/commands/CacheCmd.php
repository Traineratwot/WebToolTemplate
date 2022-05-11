<?php

	namespace model\cli\commands;

	use model\cli\types\CacheEnum;
	use model\main\Utilities;
	use Traineratwot\Cache\Cache;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;

	class CacheCmd extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "Управление кешем";
		}

		public function setup()
		{
			$this->registerParameter('action', 0, CacheEnum::class, "info - выведет игформацию о с кеше, autoClear - очистит устаревшие записи, clear - очистит весь кеш");
		}

		public function run()
		{
			$action = $this->getArg('action');
			if (empty($action) || $action === 'info') {
				$files = Utilities::glob(WT_CACHE_PATH, '*.cache.php');
				$size  = 0;
				foreach ($files as $f) {
					$size += filesize($f);
				}
				$size  = Utilities::convertBytes($size);
				$count = count($files);
				Console::info('Size: ' . $size);
				Console::info('Count cache files: ' . $count);
			}
			if ($action === 'autoClear') {
				Cache::autoRemove();
				Console::success('ok');
			}
			if ($action === 'clear') {
				Cache::removeAll();
				Console::success('all files remove');
			}
		}
	}