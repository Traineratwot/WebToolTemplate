<?php

	namespace model\cli\types;

	use Traineratwot\PhpCli\types\TEnum;

	class LocaleEnum extends TEnum
	{
		public function enums()
		{
			return ['list', 'create', 'update'];
		}
	}