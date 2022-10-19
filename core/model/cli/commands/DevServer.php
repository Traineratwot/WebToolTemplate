<?php

	namespace model\cli\commands;

	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;

	class DevServer extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "🛠 Запускает сервис авто перезагрузки страницы";
		}

		public function run()
		{
			Console::info("DevServer started ...");
			$PORT  = escapeshellarg(Config::get('DEV_SERVER_PORT'));
			$model = escapeshellarg(WT_MODEL_PATH);
			$base  = escapeshellarg(WT_BASE_PATH);
			$cmd   = WT_JS_EXEC_CMD . ' ' . WT_MODEL_PATH . 'tools/DevServer.js ' . "model=$model base=$base port=$PORT";
			Console::info($cmd);
			exec($cmd, $out, $code);
			if ($code !== 0) {
				Console::failure(implode(PHP_EOL,$out));
			} else {
				Console::success(implode(PHP_EOL,$out));
			}
		}

		public function setup()
		{
			// TODO: Implement setup() method.
		}
	}