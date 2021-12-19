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
print_r($this->data); die;

		}
	}

	return 'Test';