<?php

	namespace model\cli\commands\make;

	use Exception;
	use model\cli\Make;
	use model\main\Core;
	use model\main\Utilities;
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
			return "Создает шаблон Класса таблицы в базе данных";
		}

		/**
		 * @throws Exception
		 */
		public function run()
		{
			$core     = Core::init();
			$table    = $this->getArg('table');
			$keyField = $this->getArg('keyField');
			if ($table) {
				$list = $core->db->getAllTables();
				$find = FALSE;
				foreach ($list as $t) {
					if (mb_strtolower($t) === mb_strtolower($table)) {
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
				$p     = WT_CLASSES_PATH . 'tables/' . $class . '.php';
				if (!file_exists($p)) {
					$p = Utilities::pathNormalize($p);
					Utilities::writeFile($p, Make::makeTable($table, $keyField));
					if (file_exists($p)) {
						include_once $p;
						$core->getObject($class);
						Console::success('ok: ' . $p);
					} else {
						Console::failure('can`t write: ' . $p);
					}
				} else {
					include_once $p;
					$core->getObject($class);
					Console::warning('Already exists');
				}
			}
		}

		public function setup()
		{
			$this->registerParameter('table', 1, TString::class, 'Введите имя таблицы в базе данных');
			$this->registerParameter('keyField', 0, TString::class, 'Имя ключегого поля по умолчанию id');
		}
	}