<?php

	namespace core\page;

	use core\model\Err;
	use core\model\Page;

	class Login extends Page
	{
		public $alias = 'login';

		public function beforeRender(){
			Err::warning('test');
		}
	}

	return 'Login';