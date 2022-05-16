<?php

	namespace cron;

	use model\main\Core;
	use model\main\CoreObject;

	class CreteAllTable extends CoreObject
	{
		function process()
		{
			$tables = $this->core->db->getTablesList();
			chdir(WT_BASE_PATH);
			foreach ($tables as $table) {
				exec('wt maketable ' . $table);
			}
		}
	}

	$core = Core::init();
	(new CreteAllTable($core))->process();