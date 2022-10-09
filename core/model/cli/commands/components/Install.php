<?php

	namespace model\cli\commands\components;

	use core\model\components\Manifest;
	use model\cli\types\FilePath;
	use model\main\Core;
	use model\main\Utilities;
	use PhpZip\Exception\ZipException;
	use PhpZip\ZipFile;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;

	class Install extends Cmd
	{
		private string $manifest;

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "💾 Установка компонента";
		}

		public function setup()
		{
			$this->registerParameter('path', 1, FilePath::class, "путь до архива компонента");
		}

		/**
		 * @throws ZipException
		 */
		public function run()
		{
			$path          = $this->getArg('path');
			$componentName = Utilities::baseName($path);
			$manifest      = "components\\{$componentName}\\{$componentName}";
			if (class_exists($manifest)) {
				Console::success($componentName . ' Already installed');
				return;
			}
			$zip = new ZipFile();
			$zip->openFile($path);
			$zip->extractTo(Config::get('COMPONENTS_PATH'));
			spl_autoload($manifest);
			if (!class_exists($manifest)) {
				Console::failure('Ошибка');
				return;
			}

			/** @var manifest $component */
			$component = new $manifest(Core::init());
			$component->install();
			Console::success('ok');
		}
	}