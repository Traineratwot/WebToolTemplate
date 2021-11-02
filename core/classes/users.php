<?php

	namespace core\classes;

	use core\model\bdObject;

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
	}