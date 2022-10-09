<?php

	namespace model\cli\commands;

	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\types\TBool;

	class ErrorCmd extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "💢 Вывести лог ошибок";
		}

		public function run()
		{
			$error = Config::get('CACHE_PATH') . 'error.log';
			if (!is_null($this->getArg('clear')) && file_exists($error)) {
				unlink(Config::get('CACHE_PATH') . 'error.log');
			}
			$i = 0;
			if (file_exists($error)) {
				$f = fopen($error, 'rb');
				while (($buffer = fgets($f, 4096)) !== FALSE) {
					$i++;
					$buffer = trim($buffer);
					if (strpos($buffer, '[error]') !== FALSE) {
						Console::failure($i . '. ' . $buffer);
					} elseif (strpos($buffer, '[warning]') !== FALSE) {
						Console::warning($i . '. ' . $buffer);
					} elseif (strpos($buffer, '[info]') !== FALSE) {
						Console::success($i . '. ' . $buffer);
					} else {
						self::note($i . '. ' . $buffer);
					}
				}
			}

			if (!$i) {
				Console::success('empty logs');
			}
		}

		static function note($t)
		{
			$t = ucfirst($t);
			echo '-' . $t . "\n";
		}

		public function setup()
		{
			$this->registerOption('clear', 'c', 0, TBool::class, 'Очистить файл ошибок');
		}
	}