<?php

	namespace model\cli\commands\components;

	use model\cli\types\FilePath;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\types\TString;

	class Install extends Cmd
	{

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

		public function run()
		{
			$path = $this->getArg('path');
			Console::success('ok');
		}
	}