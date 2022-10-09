<?php

	namespace model\cli\types;

	use Exception;
	use Traineratwot\PhpCli\types\TString;

	class FileName extends TString
	{
		/**
		 * @throws Exception
		 */
		public function validate($value)
		{
			if (!is_string($value)) {
				return "Invalid value: must be a string";
			}
			$value_ = preg_replace('/\W+/m', '', $value);
			if ($value_ !== $value) {
				return "Invalid string: file name must`t contains this character: '\\ / : * ? \" > < | ' ( ) ~ - } { [ ] + = and more ...";
			}
			$value_ = preg_replace('/[^[:ascii:]]+/m', '', $value);
			if ($value_ !== $value) {
				return "Invalid string: file name must contains only ASCII characters";
			}
			return TRUE;
		}
	}

