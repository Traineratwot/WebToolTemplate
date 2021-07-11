<?php

	namespace core\ajax;

	use core\model\Ajax;
	use core\model\util;

	class logout extends Ajax
	{
		function process()
		{
			util::setCookie('authKey', NULL);
			return $this->success('Ok');
		}
	}

	return 'logout';