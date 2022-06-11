<?php

	namespace core\model\cli\commands;

	use model\main\Utilities;
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
			// TODO: Implement help() method.
		}

		public function run()
		{
			$files   = Utilities::glob(Config::get('BASE_PATH'), '*.php');
			$count   = count($files);
			$i       = 0;
			$plugins = [];
			foreach ($files as $file) {
				$i++;

				if (stripos($file, 'vendor') !== FALSE) {
					continue;
				}
				$content = file_get_contents($file);
				if (preg_match_all("@Event::emit\(['\"`](\w+)['\"`]@iu", $content, $match)) {
					foreach ($match[1] as $plugin) {
						$plugins[$plugin]['files'][] = str_replace(Config::get('BASE_PATH'), '', $file);
					}
				};
				Console::progress('FindPlugins', $i, $count);
			};
			echo "\n";
			foreach ($plugins as $plugin => $data) {
				Console::info($plugin . ": [" . implode(', ', $data['files']) . ']');
			}
		}

		public function setup()
		{
			// TODO: Implement setup() method.
		}
	}