<?php

	namespace core\classes;
	use core\bdObject;

	/**
	 * Класс для работы с таблицей `users`
	 *
	 * вызывается core::getUser()
	 */
	class user extends bdObject
	{
		public $table = 'users';
		public $primaryKey = 'id';
	}