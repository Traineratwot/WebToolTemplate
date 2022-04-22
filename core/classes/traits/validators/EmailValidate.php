<?php

	namespace traits\validators;

	trait EmailValidate
	{
		/**
		 * @throws ExceptionValidate
		 */
		public static function emailValidate($value)
		{
			$value = strip_tags($value);
			if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
				throw new ExceptionValidate('invalid email');
			}
			return $value;
		}
	}