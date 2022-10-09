<?php

	namespace model\cli\commands\components\make;

	use model\cli\commands\components\Make;
	use core\model\cli\types\ComponentsEnum;
	use model\main\Utilities;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\types\TString;

	class MakePage extends Cmd
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
			$component = $this->getArg('component');
			$url       = $this->getArg('url');
			$template  = $this->getArg('template') ?: 'base';
			$url       = Make::pathFileUcFirst($url);
			$p         = Utilities::pathNormalize(Config::get('COMPONENTS_PATH') . $component . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $url. '.php');
			$p2        = Utilities::pathNormalize(Config::get('COMPONENTS_PATH') . $component . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $url. '.tpl');
			if (!file_exists($p)) {
				if (Utilities::writeFile($p, Make::makePageClass($component, $url))) {
					Console::success('ok: ' . $p);
				} else {
					Console::failure('can`t write file: ' . $p);
				}
			} else {
				Console::failure('Already exists: ' . $p);
			}
			if (!file_exists($p2)) {
				if (Utilities::writeFile($p2, Make::makePageTpl($component, $url, $template))) {
					Console::success('ok: ' . $p2);
				} else {
					Console::failure('can`t write file: ' . $p);
				}
			} else {
				Console::failure('Already exists: ' . $p2);
			}
		}

		public function setup()
		{
			$this->registerParameter('component', 1, ComponentsEnum::class, "Имя компонента");
			$this->registerParameter('url', 1, TString::class, "url будующей страницы, помните что роутер НЕ учитвает регистер");
			$this->registerParameter('template', 0, TString::class, 'template по умолчани "base"');
		}
	}