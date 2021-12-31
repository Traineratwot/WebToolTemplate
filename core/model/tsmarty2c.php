#!/usr/bin/env php
<?php

	namespace core\model;
	require_once dirname(__DIR__) . '/config.php';
	require_once __DIR__ . '/engine.php';

	class PoUpdate
	{
		/**
		 * @var false
		 */
		private bool   $oldFile = FALSE;
		private string $domain;
		private        $lang;

		public function __construct($lang)
		{
			$this->domain = WT_LOCALE_DOMAIN;
			$this->lang   = $lang;
			if (FALSE) {
				$this->oldFile = TRUE;
			}
		}
	}