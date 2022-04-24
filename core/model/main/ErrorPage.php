<?php

	namespace model\main;

	interface ErrorPage
	{
		public function errorPage($code, $msg);
	}