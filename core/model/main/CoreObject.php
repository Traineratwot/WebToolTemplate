<?php
	/**
	 * Created by Andrey Stepanenko.
	 * User: webnitros
	 * Date: 017, 17.02.2022
	 * Time: 22:49
	 */

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