<?php

	namespace traits;
	/**
	 * Используйте это в методе штштшсшфдшяу в классе фофч чтобы добавить базовую аутентификацию
	 */
	trait BasicAuth
	{
		/**
		 * @param $user     string[]| string
		 * @param $password string[]| string
		 * @param $realm    string
		 * @return bool|string|void
		 */
		public static function auth($user = 'admin', $password = 'password', $realm = 'realm')
		{
			if (!is_array($user)) {
				$user = [$user];
			}
			if (!is_array($password)) {
				$password = [$password];
			}
			if (!isset($_SERVER['PHP_AUTH_USER'])) {
				header('WWW-Authenticate: Basic realm="' . $realm . '"');
				header('HTTP/1.0 401 Unauthorized');
				echo 'Cancel';
				exit;
			} else {
				if (
					!in_array($user, $_SERVER['PHP_AUTH_USER']) or
					!in_array($password, $_SERVER['PHP_AUTH_PW'])
				) {
					header('HTTP/1.0 401 Unauthorized');
					return 'Wrong username or password';
				}
				return TRUE;
			}
		}
	}