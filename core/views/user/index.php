<?php

	namespace page\user;

	use model\page\Page;

	class index extends Page
	{
		public $title = 'user/index';

		public function beforeRender()
		{
		}
	}

	return index::class;