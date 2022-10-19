<?php

	namespace model\cli\commands;

	use model\main\Utilities;
	use Traineratwot\Cache\Cache;
	use Traineratwot\Cache\CacheException;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;

	class FindPlugins extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "🔍 Находит плагиный в проекте";
		}

		/**
		 * @throws CacheException
		 */
		public function run(bool $silent = FALSE)
		{
			$files   = Utilities::glob(Config::get('BASE_PATH'), '*.php');
			$i       = 0;
			$plugins = [];
			foreach ($files as $key => $file) {
				if (stripos($file, 'vendor') !== FALSE) {
					unset($files[$key]);
				}
			}
			$count = count($files);
			foreach ($files as $file) {
				$found = FALSE;
				$i++;
				$content = file_get_contents($file);
				if (preg_match_all("@Event::emit\(['\"`](\w+)['\"`]@iu", $content, $match)) {
					foreach ($match[1] as $plugin) {
						$found                       = TRUE;
						$plugins[$plugin]['files'][] = str_replace(Config::get('BASE_PATH'), '', $file);
					}
				}
				if (!$silent) {
					Console::progress('FindPlugins', $i, $count, $found ? 'green' : 'white');
				}

			}
			if (!$silent) {
				Console::progress('FindPlugins', $count, $count);
				echo "\n";
			}
			if (!$silent) {
				foreach ($plugins as $plugin => $data) {
					Console::info($plugin . ": [" . implode(', ', $data['files']) . ']');
				}
				Cache::setCache('pluginsList', $plugins, 600, 'cli');
			}
			return $plugins;
		}

		public function setup()
		{
		}
	}