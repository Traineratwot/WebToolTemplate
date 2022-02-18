<?php

	namespace page;

	use model\page\Page;

	class ForgotPassword extends Page
	{
		public $alias = 'user/ForgotPassword';
		public $title = 'Forgot Password';

		public function beforeRender()
		{

		}
	}

	return 'ForgotPassword';