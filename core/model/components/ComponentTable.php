<?php

	namespace model\components;

	use model\main\BdObject;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	abstract class ComponentTable extends BdObject
	{

		public function createTable()
		: PDOENewDbObject|false
		{
			return FALSE;
		}
	}