<?php

	namespace model\page;

	use model\main\Core;

	class TmpPage extends Page
	{
		public function __construct(Core $core, $alias, $data = [])
		{
			$this->alias = $alias;
			parent::__construct($core, $data);
		}
	}