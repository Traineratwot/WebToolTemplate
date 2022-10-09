<?php

	namespace model\cli\commands\components;

	use model\cli\types\FileName;
	use model\main\Utilities;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;

	class Create extends Cmd
	{

		public array   $structure
			= [
				'classes/ajax',
				'classes/plugins',
				'classes/tables',
				'classes/traits',
				'model',
				'pages',
				'views',
			];
		private string $name;
		private string $path;

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
			$this->name = ucfirst($this->getArg('name'));
			$this->path = Config::get('COMPONENTS_PATH') . $this->name . DIRECTORY_SEPARATOR;
			if (is_dir($this->path)) {
				Console::failure($this->name . ' already exists');
				return;
			}
			$this->createFolders();
			$this->createManifest();

			Console::success('Ok ' . $this->path);
		}

		public function createFolders()
		{
			foreach ($this->structure as $folder) {
				Utilities::mkdirs($this->path . $folder);
			}
		}

		public function createManifest()
		{
			$content = Make::makeComponentManifest($this->name);
			file_put_contents($this->path . $this->name . '.php', $content);
		}
	}