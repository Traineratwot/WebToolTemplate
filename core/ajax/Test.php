<?php

	namespace ajax;


	use model\page\Ajax;

	class Test extends Ajax
	{
		function process()
		{
			return $this->success('I am AJAX', $this->data);
		}
	}

	return 'Test';