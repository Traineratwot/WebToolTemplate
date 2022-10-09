<?php

	namespace core\model\cli\commands\components;

	use model\cli\types\FileName;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;

	class Init extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "➕ Создать компонента";
		}

		public function setup()
		{
			$this->registerParameter('name', 1, FileName::class, "Имя нового компонента");
		}

		public function run()
		{
			$path = ucfirst($this->getArg('path'));
			Console::success('ok');
		}
	}