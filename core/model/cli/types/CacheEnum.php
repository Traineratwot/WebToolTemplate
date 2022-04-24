<?php

	namespace model\cli\types;

	use Traineratwot\PhpCli\types\TEnum;

	class CacheEnum extends TEnum
	{
		public function enums()
		{
			return ['info', 'autoClear', 'clear'];
		}
	}