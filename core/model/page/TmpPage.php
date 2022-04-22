<?php

	namespace model\page;

	use Exception;
	use model\main\Core;

	class TmpPage extends Page
	{
		/**
		 * @throws Exception
		 */
		public function __construct(Core $core, $alias, $data = [], $source = NULL)
		{
			$this->alias  = $alias;
			$this->source = $source;
			parent::__construct($core, $data);
		}
	}