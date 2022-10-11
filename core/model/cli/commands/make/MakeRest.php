<?php

	namespace model\cli\commands\make;

	use core\model\cli\commands\Make;
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
			return "🌐 Создает ajax";
		}

		public function run()
		{
			$url  = $this->getArg('url');
			$type = $this->getArg('type') ?: 'any';
			if (!str_contains($url, '.php')) {
				$path = $url . '.php';
			} else {
				$path = $url;
				$url  = substr($url, 0, -4);
			}
			$path = Make::pathFileUcFirst($path);
			$path = Utilities::pathNormalize(Config::get('AJAX_PATH') . $path);
			if (!file_exists($path)) {
				Utilities::writeFile($path, Make::makeAjax($url, $type));
				Console::success('ok: ' . $path);
			} else {
				Console::failure('Already exists, "' . $path . '"');
			}
		}

		public function setup()
		{
			$this->registerParameter('url', 1, TString::class, 'url будующего REST-метода, помните что роутер НЕ учитвает регистер');
			$this->registerParameter('type', 0, AjaxEnum::class, 'Тип запроса умолчанию "any"');
		}
	}