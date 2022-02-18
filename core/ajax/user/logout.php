<?php

	namespace ajax;


	use model\page\Ajax;

	class logout extends Ajax
	{
		function process()
		{
			if ($this->core->isAuthenticated) {
				$this->core->user->logout();
			}
		}
	}

	return 'logout';