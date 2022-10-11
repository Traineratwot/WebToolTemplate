<?php

	namespace model\cli\commands\make;

	use core\model\cli\commands\Make;
	use model\main\Utilities;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\types\TString;

	class MakePlugin extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "🔌 Создает плагин";
		}

		public function run()
		{
			$cls = $this->getArg('path');
			if (strpos($cls, '.php') === FALSE) {
				$path = $cls . '.php';
			} else {
				$path = $cls;
				$cls  = substr($cls, 0, -4);
			}
			$path = Make::pathFileUcFirst($path);
			$path = Utilities::pathNormalize(Config::get('PLUGINS_PATH') . $path);
			if (!file_exists($path)) {
				Utilities::writeFile($path, Make::makePlugin($cls));
				Console::success('ok: ' . $path);
			} else {
				Console::failure('Already exists, "' . $path . '"');
			}
		}

		public function setup()
		{
			$this->registerParameter('path', 1, TString::class, 'Путь до будующего плагина eg: BeforeAppInit, myCategory/AfterMyEmit');
		}
	}