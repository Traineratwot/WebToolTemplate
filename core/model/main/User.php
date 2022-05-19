<?php
	/**
	 * Created by Kirill Nefediev.
	 * User: Traineratwot
	 * Date: 19.05.2022
	 * Time: 10:46
	 */

	namespace model\main;

	abstract class User extends BdObject
	{
		abstract public function sendMail($subject = '', $body = '', $file = [], $options = []);

		abstract public function getGravatar($size = 80, $default = 'mp', $rating = 'g')
		: string;

		abstract public function getEmail()
		: string;

		abstract public function getName()
		: string;

		public function logout()
		{
			return FALSE;
		}

		public function setPassword(string $password)
		{
			return FALSE;
		}

		public function login()
		{
			return FALSE;
		}

		abstract function verifyPassword(string $password)
		: bool;
	}