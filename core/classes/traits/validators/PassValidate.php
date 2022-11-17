<?php

	namespace traits\validators;

	trait PassValidate
	{
		/**
		 * @throws ExceptionValidate
		 */
		public static function passValidate($value, $length = 6)
		{
			$value = strip_tags($value);
			if (preg_match('/^.*(?=.{' . $length . ',})(?=.*[a-zA-Z])(?=.*\d)(?=.*[!#$%&? "]).*$/')) {
				throw new ExceptionValidate('invalid password');
			}
			return $value;
		}
	}