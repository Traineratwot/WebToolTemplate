<?php

	namespace core\model\Events\plugins;

	use model\main\Core;

	interface AfterAppDataBaseInit
	{
		public function process(Core $core);
	}
