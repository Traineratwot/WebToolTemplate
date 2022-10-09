<?php

	namespace core\model\components;

	use Traineratwot\PDOExtended\PDOE;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	abstract class ComponentTable
	{
		abstract public static function table()
		: string;

		public function createTable()
		: PDOENewDbObject
		{
			return PDOE::createTable(self::table());
		}
	}