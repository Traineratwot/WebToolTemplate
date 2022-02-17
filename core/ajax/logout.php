<?php

	namespace ajax;


	use model\page\Ajax;

	class logout extends Ajax
	{
		function process()
		{
			session_unset();
			return $this->success('Ok');
		}
	}

	return 'logout';