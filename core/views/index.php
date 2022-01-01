<?php

	namespace core\page;

	use core\model\Page;

	class Index extends Page
	{
		public $alias = 'index';
		public $title = 'index';

		public function beforeRender()
		{
//			if ($this->core->user == NULL) {
//				$this->redirect('login');
//			} else {
//				$this->forward('profile');
//			}
		}
	}

	return 'Index';