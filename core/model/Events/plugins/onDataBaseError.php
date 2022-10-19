<?php

	namespace core\model\Events\plugins;

	use Exception;
	use model\main\Core;

	interface onDataBaseError
	{
		public function process(Core $core, Exception $e);
	}
