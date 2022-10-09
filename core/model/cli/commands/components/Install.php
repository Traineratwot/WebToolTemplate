<?php

	namespace model\cli\commands\components;

	use core\model\composer\Composer;
	use model\cli\types\FilePath;
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
			$path = $this->getArg('path');
			$zip  = new ZipFile();
			$zip->openFile($path);
			$zip->extractTo(Config::get('COMPONENTS_PATH'));
			$componentName = Utilities::baseName($path);
			$manifest      = "core\components\\{$componentName}\\{$componentName}";
			if (!class_exists($manifest)) {
				Console::failure('Ошибка');
				return;
			}
			$this->installComposer($manifest);
			Console::success('ok');
		}

		public function installComposer($manifest)
		{
			$composer = $manifest::getComposerPackage();
			if (!empty($composer)) {
				foreach ($composer as $value) {
					Composer::require($value);
				}
			}
		}

	}