<?php

	namespace classes\tables;

	use model\bdObject;
	use model\Err;
	use model\util;

	/**
	 * Класс для работы с таблицей `users`
	 * вызывается core::getObject('Users')
	 */
	class Users extends bdObject
	{
		public $table      = 'users';
		public $primaryKey = 'id';

		public function sendMail($subject, $body = '', $file = [])
		{
			return $this->core->mail($this, $subject, $body, $file);
		}

		/**
		 * Get either a Gravatar URL or complete image tag for a specified email address.
		 *
		 * @param string $email The email address
		 * @param string $s     Size in pixels, defaults to 80px [ 1 - 2048 ]
		 * @param string $d     Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
		 * @param string $r     Maximum rating (inclusive) [ g | pg | r | x ]
		 * @return String containing either just a URL or a complete image tag
		 * @source https://gravatar.com/site/implement/images/php/
		 */
		function getGravatar($s = 80, $d = 'mp', $r = 'g')
		{
			$url = 'https://www.gravatar.com/avatar/';
			$url .= md5(strtolower(trim($this->get('email'))));
			$url .= "?s=$s&d=$d&r=$r";
			return $url;
		}

		public function login()
		{
			WT_RESTART_SESSION_FUNCTION();
			$_SESSION['authKey'] = $this->get('authKey');
			$_SESSION['ip']      = util::getIp();
			$hash                = hash('sha256', $_SESSION['authKey'] . $_SESSION['ip']);
			setCookie('authKey', $hash, time() + 3600 * 24 * 30, '/');
			setCookie('userId', $this->get('id'), time() + 3600 * 24 * 30, '/');
		}

		public function logout()
		{
			WT_RESTART_SESSION_FUNCTION();
		}

		public function register($email, $password = NULL)
		{
			if ($this->isNew()) {
				if (!$password) {
					$password = util::id(8);
				}
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					Err::fatal('Please enter a valid email', __FILE__, __FILE__);
				}
				if (strlen($password) < 6) {
					Err::fatal('Please enter a password length >= 6 characters', __FILE__, __FILE__);
				}
				$salt    = util::id(8);
				$pass    = $password . $salt;
				$authKey = hash('sha256', $email . $pass);
				$this->set('email', $email);
				$this->set('password', hash('sha256', $pass));
				$this->set('salt', $salt);
				$this->set('authKey', $authKey);
				$this->save();
				if ($this->isNew()) {
					Err::fatal('Failed write to DataBase', __FILE__, __FILE__);
				} else {
					$this->login();
					return TRUE;
				}
			}
		}
	}