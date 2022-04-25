<?php

	namespace model\cli\types;

	use model\main\Utilities;
	use Traineratwot\PhpCli\types\TString;

	class FilePath extends TString
	{
		public function validate($value)
		{
			$value = Utilities::pathNormalize(WT_CRON_PATH . 'controllers/' . $value);
			return file_exists($value) ?: 'Invalid path "' . $value . '" ';
		}
	}