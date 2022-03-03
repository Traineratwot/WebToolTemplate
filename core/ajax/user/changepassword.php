<?php

	namespace ajax;

	use model\page\Ajax;
	use traits\Utilities;

	class ChangePassword extends Ajax
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
					$salt        = random_int(1000000, 9999999);
					$authKey     = md5($this->password . $this->email . $salt);
					$this->email = $this->core->user->get('email');
					$this->core->user->set('password', self::hash($this->password . $salt));
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

	return 'ChangePassword';