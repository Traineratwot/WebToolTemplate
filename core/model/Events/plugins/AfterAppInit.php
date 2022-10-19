<?php

	namespace core\model\Events\plugins;

	use model\main\Core;

	interface AfterAppInit
	{
		public function process(Core $core);
	}
