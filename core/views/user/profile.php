<?php

	namespace page;

	use model\page\Page;

	class Profile extends Page
	{
		public $alias = 'user/profile';

		public function beforeRender()
		{

		}
	}

	return 'Profile';