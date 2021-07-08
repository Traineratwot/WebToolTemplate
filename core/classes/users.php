<?php

	namespace core\classes;
	use core\model\bdObject;

	/**
	 * Класс для работы с таблицей `users`
	 *
	 * вызывается core::getUser()
	 */
	class User extends bdObject
	{
		public $table = 'users';
		public $primaryKey = 'id';
	}