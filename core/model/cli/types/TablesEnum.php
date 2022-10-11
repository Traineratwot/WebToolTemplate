<?php

	namespace model\cli\types;

	use model\main\Core;
	use Traineratwot\PhpCli\types\TEnum;

	class TablesEnum extends TEnum
	{
		public function enums()
		{
			return (Core::init())->db->getTablesList();
		}
	}