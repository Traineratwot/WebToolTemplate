<?php

	namespace model\page;

	use model\main\Core;

	/**
	 * Класс для Чанка
	 */
	class Chunk extends Page
	{
		public function __construct(Core $core, $alias, $values)
		{
			$this->alias = $alias;
			parent::__construct($core);
			foreach ($values as $key => $value) {
				$this->setVar($key, $value);
			}
		}

		public function render($return = TRUE)
		{
			return parent::render($return);
		}
	}
