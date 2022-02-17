<?php

	namespace page;

	use model\Err;
	use model\Page;

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