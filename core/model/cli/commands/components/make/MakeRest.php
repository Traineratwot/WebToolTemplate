<?php

	namespace model\cli\commands\components\make;

	use model\cli\commands\components\Make;
	use core\model\cli\types\ComponentsEnum;
	use model\cli\types\AjaxEnum;
	use model\main\Utilities;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\types\TString;

	class MakeRest extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "Создает ajax";
		}

		public function run()
		{
			$component = $this->getArg('component');
			$url       = $this->getArg('url');
			$type      = $this->getArg('type') ?: 'any';
			if (!str_contains($url, '.php')) {
				$path = $url . '.php';
			} else {
				$path = $url;
				$url  = substr($url, 0, -4);
			}
			$path = Make::pathFileUcFirst($path);
			$path = Utilities::pathNormalize(Config::get('COMPONENTS_PATH') . $component . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'ajax' . DIRECTORY_SEPARATOR . $path);
			if (!file_exists($path)) {
				Utilities::writeFile($path, Make::makeAjax($component, $url, $type));
				Console::success('ok: ' . $path);
			} else {
				Console::failure('Already exists, "' . $path . '"');
			}
		}

		public function setup()
		{
			$this->registerParameter('component', 1, ComponentsEnum::class, "Имя компонента");
			$this->registerParameter('url', 1, TString::class, 'url будующего REST-метода, помните что url всегда начинается с имени компонента');
			$this->registerParameter('type', 0, AjaxEnum::class, 'Тип запроса умолчанию "any"');
		}
	}