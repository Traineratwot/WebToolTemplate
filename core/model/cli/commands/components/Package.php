<?php

	namespace core\model\cli\commands\components;

	use core\model\cli\types\ComponentsEnum;
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

		public function run()
		{
			$path = $this->getArg('path');
			Console::success('ok');
		}
	}