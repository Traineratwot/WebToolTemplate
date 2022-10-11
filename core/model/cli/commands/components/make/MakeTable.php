<?php

	namespace model\cli\commands\components\make;

	use core\model\cli\types\ComponentsEnum;
	use Exception;
	use model\cli\commands\components\Make;
	use model\cli\types\TablesEnum;
	use model\main\Core;
	use model\main\Utilities;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\TypeException;
	use Traineratwot\PhpCli\types\TString;

	class MakeTable extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "🥫 Создает шаблон Класса таблицы в базе данных";
		}

		/**
		 * @throws Exception
		 */
		public function run()
		{
			$core      = Core::init();
			$component = $this->getArg('component');
			$table     = $this->getArg('table');
			$keyField  = $this->getArg('keyField') ?: 'id';
			if ($table) {
				$list = $core->db->getTablesList();
				$find = FALSE;
				foreach ($list as $t) {
					if (strtolower($t) === strtolower($table)) {
						$find  = TRUE;
						$table = $t;
						break;
					}
				}
				if (!$find) {
					foreach ($list as $t) {
						Console::info($t);
					}
					throw new TypeException("Table '$table' does not exist. Chose table from this list");
				}
				$class = Make::name2class($table);

				$p = Make::pathFileUcFirst($class);
				$p = Utilities::pathNormalize(Config::get('COMPONENTS_PATH') . $component . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR . $p . '.php');
				if (!file_exists($p)) {
					$p = Utilities::pathNormalize($p);

					Utilities::writeFile($p, Make::makeTable($component, $table, $keyField, $namespace_class));
					if (file_exists($p)) {
						spl_autoload($namespace_class);
						if (class_exists($namespace_class)) {
							Console::success('ok: ' . $p);
						} else {
							Console::failure('err: ' . $p);
						}
					} else {
						Console::failure('can`t write: ' . $p);
					}
				} else {
					Console::warning('Already exists');
				}
			}
		}

		public function setup()
		{
			$this->registerParameter('component', 1, ComponentsEnum::class, "Имя компонента");
			$this->registerParameter('table', 1, TablesEnum::class, 'Введите имя таблицы в базе данных');
			$this->registerParameter('keyField', 0, TString::class, 'Имя ключегого поля по умолчанию id');
		}
	}