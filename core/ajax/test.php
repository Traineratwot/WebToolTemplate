<?php

	namespace core\ajax;

	use core\model\Ajax;

	class Test extends Ajax
	{
		function process()
		{
			//TODO YOU CODE
			return $this->success('I am AJAX' , $this->data);
		}
	}

	return 'Test';