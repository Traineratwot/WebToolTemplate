<?php

	namespace ajax;


	use model\page\Ajax;

	class Test extends Ajax
	{
		function process()
		{
			//TODO YOU CODE
			return $this->success('I am AJAX' , $this->data);
		}
	}

	return 'Test';