<?php
	namespace traits\validators;

	trait passValidate
	{
		public static function passValidate($value, $length = 6)
		{
			$value = trip_tags($value);
			if (preg_match('/^.*(?=.{' . $length . ',})(?=.*[a-zA-Z])(?=.*\d)(?=.*[!#$%&? "]).*$/')) {
				throw new ExceptionValidate('invalid password');
			}
			return $value;
		}
	}