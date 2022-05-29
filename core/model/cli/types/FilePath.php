<?php

	namespace model\cli\types;

	use model\main\Utilities;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\types\TString;

	class FilePath extends TString
	{
		public function validate($value)
		{
			$value = Utilities::pathNormalize(Config::get('CRON_PATH') . 'controllers/' . $value);
			return file_exists($value) ?: 'Invalid path "' . $value . '" ';
		}
	}