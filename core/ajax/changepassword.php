<?php

	namespace ajax;

	use classes\traits\Utilities;
	use model\page\Ajax;

	class Changepassword extends Ajax
	{
		use Utilities;

		public function initialize()
		{
			$this->password = strip_tags($_REQUEST['password']);
			return TRUE;
		}

		function post()
		{
			$this->core->auth();
			if ($this->core->isAuthenticated) {
				if ($this->password) {
					$this->core->user->set('password', self::hash($this->password));
					$salt        = random_int(1000000, 9999999);
					$this->email = $this->core->user->get('email');
					$authKey     = md5($this->password . $this->email . $salt);
					$this->core->user->set('salt', $salt);
					$this->core->user->set('authKey', $authKey);
					$this->core->user->save();
					session_unset();
					return $this->success('Ok');
				} else {
					return $this->failure('empty password');
				}
			} else {
				return $this->failure('ошибка');
			}
		}
	}

	return 'changepassword';