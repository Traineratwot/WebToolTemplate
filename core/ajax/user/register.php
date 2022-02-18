<?php

	namespace ajax;

	use model\main\Err;
	use model\page\Ajax;
	use model\util;

	class register extends Ajax
	{
		public function initialize()
		{
			$this->email    = strip_tags($_REQUEST['email']);
			$this->password = strip_tags($_REQUEST['password']);
			if ($this->email and $this->password) {
				return TRUE;
			} else {
				return 'empty email or password';
			}
		}

		public function process()
		{
			try {
				$newUser = $this->core->getUser(['email' => $this->email]);
				if ($newUser->isNew()) {
					$newUser->register($this->email, $this->password);
				} else {
					return $this->failure('User already exists');
				}
			} catch (\Exception $e) {
				return $this->failure($e->getMessage());
			}
		}
	}

	return 'register';