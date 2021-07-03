<?php

	namespace core\modules;

	use core\Err;

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