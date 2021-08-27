<?php

	namespace core\ajax;

	use core\model\Ajax;
	use core\model\Err;
	use core\model\util;

	class register extends Ajax
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

		public function process()
		{
			try {
				$newUser = $this->core->getUser(['email' => $this->email]);
				if ($newUser->isNew()) {
					if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
						Err::fatal('Please enter a valid email', __FILE__, __FILE__);
					}
					if (strlen($this->password) < 6) {
						Err::fatal('Please enter a password length >= 6 characters', __FILE__, __FILE__);
					}
					$salt = random_int(1000000, 9999999);
					$authKey = md5($this->password . $this->email . $salt);
					/** @var Core $newUser */
					$newUser->set('email', $this->email);
					$newUser->set('password', md5($this->password));
					$newUser->set('salt', $salt);
					$newUser->set('authKey', $authKey);
					$newUser->save();
					if ($newUser->isNew()) {
						Err::fatal('Failed write to DataBase', __FILE__, __FILE__);
					} else {
						util::setCookie('authKey', $authKey);
						return $this->success('Ok');
					}
				} else {
					return $this->failure('User already exists');
				}
			} catch (\Exception $e) {
				return $this->failure($e->getMessage());
			}
		}
	}

	return 'register';