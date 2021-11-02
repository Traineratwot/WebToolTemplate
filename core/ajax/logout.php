<?php

	namespace core\ajax;

	use core\model\Ajax;

	class logout extends Ajax
	{
		function process()
		{
			session_unset();
			return $this->success('Ok');
		}
	}

	return 'logout';