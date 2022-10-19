<?php

	namespace core\model\Events\plugins;

	use Traineratwot\PhpCli\CLI;

	interface RegisterCmd
	{
		public function process(CLI $cli);
	}
