<?php

	namespace core\ajax;

	use core\model\Ajax;

	class Test extends Ajax
	{
		function process()
		{
			//TODO YOU CODE
			return $this->success('ok', $this->data);
		}
	}

	return 'Test';