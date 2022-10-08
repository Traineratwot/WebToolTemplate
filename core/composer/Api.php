<?php

	namespace core\composer;

	class Api
	{
		/**
		 * @noinspection PhpSameParameterValueInspection
		 * @noinspection RedundantSuppression
		 */
		private static function execute(string $cmd, string &$out = null)
		: bool
		{
			$out  = '';
			$exec = WT_PHP_EXEC_CMD . ' ' . WT_COMPOSER_EXEC_PATH . ' ' . $cmd;
			exec($exec, $out, $code);
			return $code === 0;
		}

		public static function require(string $package)
		{
			return self::execute("require $package ");
		}

		public static function remove(string $package)
		{
			return self::execute("remove $package ");
		}

		public static function update()
		{
			return self::execute("update");
		}

		public static function getAllConfigs()
		{
			self::execute("getAllConfigs", $out);
			return $out;
		}

		public static function configUpdate()
		{
			return self::execute("configUpdate");
		}
	}