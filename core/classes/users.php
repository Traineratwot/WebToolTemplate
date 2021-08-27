<?php

	namespace core\classes;

	use core\model\bdObject;

	/**
	 * Класс для работы с таблицей `users`
	 * вызывается core::getObject('Users')
	 */
	class Users extends bdObject
	{
		public $table = 'users';
		public $primaryKey = 'id';

		public function sendMail($subject, $body='', $file = [])
		{
			return $this->core->mail($this, $subject, $body, $file);
		}
	}