<?php

	namespace tables;

	use model\main\BdObject;
	use model\main\Err;
	use model\main\Utilities;


	/**
	 * Класс для работы с таблицей `users`
	 * вызывается core::getObject('Users')
	 */
	class Users extends BdObject
	{


		public $table      = 'users';
		public $primaryKey = 'id';

		public function sendMail($subject, $body = '', $file = [])
		{
			return $this->core->mail($this, $subject, $body, $file);
		}

		/**
		 * Get either a Gravatar URL || complete image tag for a specified email address.
		 *
		 * @param int    $s Size in pixels, defaults to 80px [ 1 - 2048 ]
		 * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
		 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
		 * @return String containing either just a URL || a complete image tag
		 * @source https://gravatar.com/site/implement/images/php/
		 */
		function getGravatar($s = 80, $d = 'mp', $r = 'g')
		{
			$url = 'https://www.gravatar.com/avatar/';
			$url .= md5(strtolower(trim($this->get('email'))));
			$url .= "?s=$s&d=$d&r=$r";
			return $url;
		}

		public function getName()
		{
			return $this->get('email');
		}

		public function login()
		{
			WT_RESTART_SESSION_FUNCTION();
			$_SESSION['authKey'] = $this->get('authKey');
			$_SESSION['ip']      = Utilities::getIp();
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
					$password = Utilities::id(8);
				}
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					Err::fatal('Please enter a valid email', __FILE__, __FILE__);
				}
				if (strlen($password) < 6) {
					Err::fatal('Please enter a password length >= 6 characters', __FILE__, __FILE__);
				}
				$pass = $password;
				$this->setPassword($pass);
				$authKey = hash('sha256', $email . $this->get('password'));
				$this->set('email', $email);
				$this->set('authKey', $authKey);
				$this->save();
				if ($this->isNew()) {
					Err::fatal('Failed write to DataBase', __FILE__, __FILE__);
				} else {
					$this->login();
					return TRUE;
				}
			}
			return TRUE;
		}

		public function setPassword($password)
		{
			$salt = Utilities::id(8);
			$this->set('salt', $salt);
			$password .= $salt;
			$this->set('password', password_hash($password, PASSWORD_DEFAULT));
		}

		public function verifyPassword($password)
		{
			return password_verify($password . $this->get('salt'), $this->get('password'));
		}
	}