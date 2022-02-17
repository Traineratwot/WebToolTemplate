<?php

	namespace model\page;

	class TmpPage extends Page
	{
		public function __construct(Core $core, $alias, $data = [])
		{
			$this->alias = $alias;
			parent::__construct($core, $data);
		}
	}