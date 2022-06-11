<?php
	namespace core\cron\controllers\category;
	use model\main\Core;
	use model\main\CoreObject;
	class Test extends CoreObject
	{
		function process(){
			//TODO: process
		}
	}
	$core = Core::init();
	(new Test($core))->process();