<?php

	namespace core\model\Events\plugins;

	use model\main\Core;

	interface BeforeAppInit
	{
		public function process(Core $core);
	}
