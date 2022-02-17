<?php
	/**
	 * Created by Andrey Stepanenko.
	 * User: webnitros
	 * Date: 017, 17.02.2022
	 * Time: 22:50
	 */

	namespace model\main;

	interface ErrorPage
	{
		public function errorPage($code, $msg);
	}