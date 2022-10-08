<?php

	namespace core\model\components;

	use Traineratwot\PhpCli\Console;

	abstract class Manifest
	{
		abstract public static function name()
		: string;

		abstract public static function description()
		: string;

		final public static function uninstall()
		{
			Console::time(__CLASS__ . ':' . __FUNCTION__);
			Console::info(__CLASS__ . ':beforeUninstall');
			self::beforeUninstall();
			Console::info(__CLASS__ . ':afterUninstall');
			self::afterUninstall();
			Console::timeEnd(__CLASS__ . ':' . __FUNCTION__);

		}

		final public static function install()
		{
			Console::time(__CLASS__ . ':' . __FUNCTION__);
			Console::info(__CLASS__ . ':beforeInstall');
			self::beforeInstall();
			Console::info(__CLASS__ . ':afterInstall');
			self::afterInstall();
			Console::timeEnd(__CLASS__ . ':' . __FUNCTION__);
		}

		public static function beforeInstall() { }

		public static function afterInstall()
		{
			file_put_contents(__DIR__ . '/isInstall.lock', time());
		}

		public static function beforeUninstall() { }

		public static function checkForUpdate() { }

		public static function update() { }

		public static function afterUninstall()
		{
			unlink(__DIR__ . '/isInstall.lock');
		}

		public static function isInstalled()
		{
			return file_exists(__DIR__ . '/isInstall.lock');
		}


	}