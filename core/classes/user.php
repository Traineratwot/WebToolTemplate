<?php

	namespace core\classes;

	use core\bdObject;

	/**
	 * Класс для работы с таблицей `users`
	 *
	 * вызывается core::getUser()
	 */
	class User extends bdObject
	{
		public $table = 'users';
		public $primaryKey = 'id';

		public function getLinks()
		{
			if (!$this->isNew()) {
				return $this->core->getCollection('Link', ['userId' => $this->get($this->primaryKey)]);
			} else {
				return NULL;
			}
		}
	}