<?php

	namespace model\cli\commands\components;

	use core\model\cli\types\ComponentsEnum;
	use model\main\Utilities;
	use PhpZip\Exception\ZipException;
	use PhpZip\ZipFile;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;

	class Package extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "📦 Упаковка компонента";
		}

		public function setup()
		{
			$this->registerParameter('name', 1, ComponentsEnum::class, "Имя компонента");
		}

		/**
		 * @throws ZipException
		 */
		public function run()
		{
			$p = Config::get('BASE_PATH') . 'packages' . DIRECTORY_SEPARATOR;
			Utilities::mkdirs($p);
			$name      = $this->getArg('name');
			$filename  = $p . $name . '.zip';
			$zip       = new ZipFile($filename);
			$date      = date(DATE_ATOM);
			$component = Config::get('COMPONENTS_PATH') . $name;
			$zip->addDirRecursive($component,$name.'/');
			$zip->setArchiveComment(<<<TXT
Component : $name
Date : $date
TXT
			);
			$zip->saveAsFile($filename);
			Console::success('ok');
		}
	}