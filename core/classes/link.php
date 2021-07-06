<?php

	namespace core\classes;
	use core\bdObject;

	/**
	 * Класс для работы с таблицей `users`
	 *
	 * вызывается core::getUser()
	 */
	class Link extends bdObject
	{
		public $table = 'links';
		public $primaryKey = 'id';
	}