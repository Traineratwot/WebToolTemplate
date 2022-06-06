<?php

	namespace core\cron\controllers;

	use model\main\Core;
	use model\main\CoreObject;
	use Traineratwot\config\Config;

	class CreateAllTable extends CoreObject
	{
		function process()
		{
			$tables = $this->core->db->getTablesList();
			chdir(Config::get('BASE_PATH'));
			foreach ($tables as $table) {
				exec('./wt makeTable ' . $table);
			}
		}
	}

	$core = Core::init();
	(new CreateAllTable($core))->process();