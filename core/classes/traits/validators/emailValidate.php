<?php
	/**
	 * Created by Andrey Stepanenko.
	 * User: webnitros
	 * Date: 017, 17.02.2022
	 * Time: 22:46
	 */

	namespace traits\validators;

	trait emailValidate
	{
		public static function emailValidate($value)
		{
			$value = trip_tags($value);
			if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
				throw new ExceptionValidate('invalid email');
			}
			return $value;
		}
	}