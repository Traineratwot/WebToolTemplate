<?php

	namespace ajax;

	use classes\Users;
	use model\main\Err;
	use model\page\Ajax;

	class Login extends Ajax
	{
		public function initialize()
		{
			$this->email    = strip_tags($_REQUEST['email']);
			$this->password = strip_tags($_REQUEST['password']);
			if ($this->email and $this->password) {
				return TRUE;
			} else {
				return 'пустой адрес электронной почты или пароль';
			}
		}

		public function post()
		{
			try {
				/** @var users $User */
				$User = $this->core->getUser(['email' => $this->email]);
				if (!$User->isNew()) {
					$salt = $User->get('salt');
					$pass = $this->password . $salt;
					if ($User->get('password') !== self::hash($pass)) {
						Err::fatal('Неправильный пароль', __FILE__, __FILE__);
					} else {
						$User->login();
						return $this->success('Ok');
					}
				} else {
					return $this->failure('Пользователь не существует: ' . $this->login);
				}
			} catch (\Exception $e) {
				return $this->failure($e->getMessage());
			}
		}
	}

	return 'Login';