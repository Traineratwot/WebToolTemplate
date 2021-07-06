<?php

	namespace core\classes;
	use core\bdObject;

	/**
	 * Класс для работы с таблицей `users`
	 *
	 * вызывается core::getUser()
	 */
	class Stat extends bdObject
	{
		public $table = 'stat';
		public $primaryKey = 'id';
	}