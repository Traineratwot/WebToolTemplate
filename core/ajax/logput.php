<?php

	namespace core\ajax;

	use core\model\Ajax;

	class logput extends Ajax
	{
		function process()
		{
			util::setCookie('authKey', NULL);
			return $this->success('Ok');
		}
	}

	return 'logput';