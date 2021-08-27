<?php

	namespace core\ajax;

	use core\model\Ajax;
	use core\model\Err;
	use core\model\util;

	class Login extends Ajax
	{
		public function initialize()
		{
			$this->email = strip_tags($_REQUEST['email']);
			$this->password = strip_tags($_REQUEST['password']);
			if ($this->email and $this->password) {
				return TRUE;
			} else {
				return 'empty email or password';
			}
		}

		public function post()
		{
			try {
				/** @var user $User */
				$User = $this->core->getUser(['email' => $this->email]);
				if (!$User->isNew()) {
					if ($User->get('password') !== md5($this->password)) {
						Err::fatal('Wrong password', __FILE__, __FILE__);
					} else {
						util::setCookie('authKey', $User->get('authKey'));
						return $this->success('Ok');
					}
				} else {
					return $this->failure('User not exists: '.$this->email);
				}
			} catch (\Exception $e) {
				return $this->failure($e->getMessage());
			}
		}
	}
	return 'Login';