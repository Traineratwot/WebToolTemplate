<?php
	/** @noinspection ForgottenDebugOutputInspection */

	/** @noinspection ForgottenDebugOutputInspection */

	namespace page;

	use model\page\Page;

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