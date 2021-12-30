<?php

	namespace core\page;

	use core\model\Err;
	use core\model\Page;

	class Test extends Page
	{
		public $alias = 'test';
		public $title = 'test';

		public function beforeRender(){
			echo '<pre>';
			var_dump($this->data);
			var_dump('I am page', $this->data ); die;

		}
	}

	return 'Test';