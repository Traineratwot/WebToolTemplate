<?php

	namespace model\cli\commands\make;

	use core\model\cli\commands\Make;
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
			return "📄 Создает траницу";
		}

		public function run()
		{
			$url      = $this->getArg('url');
			$template = $this->getArg('template') ?: 'base';
			$url      = Make::pathFileUcFirst($url);
			$p        = Utilities::pathNormalize(Config::get('VIEWS_PATH') . $url . '.php');
			$p2       = Utilities::pathNormalize(Config::get('PAGES_PATH') . $url . '.tpl');
			if (!file_exists($p)) {
				if (Utilities::writeFile($p, Make::makePageClass($url))) {
					Console::success('ok: ' . $p);
				} else {
					Console::failure('can`t write file: ' . $p);
				}
			} else {
				Console::failure('Already exists: ' . $p);
			}
			if (!file_exists($p2)) {
				if (Utilities::writeFile($p2, Make::makePageTpl($url, $template))) {
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
			$this->registerParameter('url', 1, TString::class, "url будующей страницы, помните что роутер НЕ учитвает регистер");
			$this->registerParameter('template', 0, TString::class, 'template по умолчани "base"');
		}
	}