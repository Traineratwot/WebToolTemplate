<?php

	namespace model\cli\commands;

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
			$model = escapeshellarg(WT_MODEL_PATH);
			$base  = escapeshellarg(WT_BASE_PATH);
			$cmd   = WT_JS_EXEC_CMD . ' ' . WT_MODEL_PATH . 'tools/DevServer.js ' . "model=$model base=$base";
			Console::info($cmd);
			exec($cmd, $out, $code);
			if ($code !== 0) {
				Console::failure($out);
			} else {
				Console::success($out);
			}
		}

		public function setup()
		{
			// TODO: Implement setup() method.
		}
	}