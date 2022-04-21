<?php
	namespace model\main;

	/**
	 * Класс для работы вместе core
	 */
	abstract class CoreObject
	{
		/**
		 * @var Core
		 */
		public Core $core;

		public function __construct(Core $core)
		{
			$this->core = &$core;
		}
	}