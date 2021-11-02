<?php

	namespace core\ajax;

	use core\model\Ajax;
	use core\model\Err;
	use core\model\util;

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
				/** @var user $User */
				$User = $this->core->getUser(['email' => $this->email]);
				if (!$User->isNew()) {
					if ($User->get('password') !== md5($this->password)) {
						Err::fatal('Неправильный пароль', __FILE__, __FILE__);
					} else {
						$_SESSION['authKey'] = $User->get('authKey');
						$_SESSION['ip']      = util::getIp();
						session_write_close();
						return $this->success('Ok');
					}
				} else {
					return $this->failure('Пользователь не существует: ' . $this->email);
				}
			} catch (\Exception $e) {
				return $this->failure($e->getMessage());
			}
		}
	}

	return 'Login';