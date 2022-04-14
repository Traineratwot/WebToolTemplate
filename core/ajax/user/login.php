<?php

	namespace ajax;

	use model\main\Err;
	use model\page\Ajax;
	use tables\Users;

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
				/** @var Users $User */
				$User = $this->core->getUser(['email' => $this->email]);
				if (!$User->isNew()) {
					if (!$User->verifyPassword($this->password)) {
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