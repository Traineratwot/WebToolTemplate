<?php

	namespace page\user;

	use model\page\Page;

	class Login extends Page
	{
		public $alias = 'user/login';

		public function beforeRender()
		{
		}
	}

	return Login::class;