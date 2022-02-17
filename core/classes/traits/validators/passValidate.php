<?php
	/**
	 * Created by Andrey Stepanenko.
	 * User: webnitros
	 * Date: 017, 17.02.2022
	 * Time: 22:46
	 */

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