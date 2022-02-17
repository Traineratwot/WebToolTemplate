<?php
	/**
	 * Created by Andrey Stepanenko.
	 * User: webnitros
	 * Date: 017, 17.02.2022
	 * Time: 22:46
	 */

	namespace traits\validators;

	use function __;

	trait emptyValidate
	{
		public static function emptyValidate($value, $strict = FALSE)
		{
			if (empty($value)) {
				throw new ExceptionValidate(__('can`t be') . ' ' . __('empty'));
			} elseif ($strict) {
				if (!(bool)$value) {
					throw new ExceptionValidate(__('can`t be') . ' ' . $value);
				}
			}
			return $value;
		}
	}