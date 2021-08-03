<?php

	namespace core\page;

	use core\model\Err;
	use core\model\Page;

	class Index extends Page
	{
		public $alias = 'index';
		public $title = 'index';

		public function beforeRender(){

		}
	}

	return 'Index';