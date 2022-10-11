<?php

	namespace model\cli\commands\components\make;

	use model\cli\commands\components\Make;
	use core\model\cli\types\ComponentsEnum;
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
			return "🔌 Создает плагин копонента";
		}

		public function run()
		{
			$component = $this->getArg('component');
			$cls = $this->getArg('path');
			if (!str_contains($cls, '.php')) {
				$path = $cls . '.php';
			} else {
				$path = $cls;
				$cls  = substr($cls, 0, -4);
			}
			$path = Make::pathFileUcFirst($path);
			$path = Utilities::pathNormalize(Config::get('COMPONENTS_PATH') . $component . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $path);
			if (!file_exists($path)) {
				Utilities::writeFile($path, Make::makePlugin($component,$cls));
				Console::success('ok: ' . $path);
			} else {
				Console::failure('Already exists, "' . $path . '"');
			}
		}

		public function setup()
		{
			$this->registerParameter('component', 1, ComponentsEnum::class, "Имя компонента");
			$this->registerParameter('path', 1, TString::class, 'Путь до будующего плагина eg: BeforeAppInit, myCategory/AfterMyEmit');
		}
	}