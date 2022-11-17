<?php

	namespace traits\validators;

	use function __;

	trait EmptyValidate
	{
		/**
		 * @throws ExceptionValidate
		 */
		public static function emptyValidate($value, $strict = FALSE)
		{
			if (empty($value)) {
				throw new ExceptionValidate(__('can`t be') . ' ' . __('empty'));
			}

			if ($strict && !(bool)$value) {
				throw new ExceptionValidate(__('can`t be') . ' ' . $value);
			}
			return $value;
		}
	}