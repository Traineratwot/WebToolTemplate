<?php

	namespace ajax\user;


	use model\page\Ajax;

	class Logout extends Ajax
	{
		function process()
		{
			if ($this->core->isAuthenticated) {
				$this->core->user->logout();
			}
			return $this->success('ok');
		}
	}

	return Logout::class;