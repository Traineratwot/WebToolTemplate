<?php

	namespace core\page;

	use core\model\Page;

	class Login extends Page
	{
		public $alias = 'login';

		public function beforeRender()
		{
			var_dump('Test');
			echo strftime("%A %e %B %Y", mktime(0, 0, 0, 12, 22, 1978));
			die;
		}
	}

	return 'Login';