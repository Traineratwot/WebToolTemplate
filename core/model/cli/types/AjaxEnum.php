<?php

	namespace model\cli\types;

	use Traineratwot\PhpCli\types\TEnum;

	class AjaxEnum extends TEnum
	{
		public function enums()
		{
			return ['any', 'post', 'get'];
		}
	}