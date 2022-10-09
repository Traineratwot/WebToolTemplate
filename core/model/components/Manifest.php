<?php

	namespace core\model\components;

	use core\model\composer\Composer;
	use model\main\CoreObject;
	use Traineratwot\PhpCli\Console;

	abstract class Manifest extends CoreObject
	{
		public static function name()
		: string
		{
			return basename(__DIR__);
		}

		abstract public static function description()
		: string;

		/**
		 * @return array<string>
		 */
		abstract public static function getComposerPackage()
		: array;

		public function beforeInstall() { }


		public function beforeUninstall() { }

		public function checkForUpdate() { }

		public function update()
		{

		}

		//start check install

		public function afterUninstall()
		{
			unlink(__DIR__ . '/isInstall.lock');
		}

		public function afterInstall()
		{
			file_put_contents(__DIR__ . '/isInstall.lock', time());
		}

		public function isInstalled()
		{
			return file_exists(__DIR__ . '/isInstall.lock');
		}

		//end check install


		final public function uninstall()
		{
			Console::time(__CLASS__ . ':' . __FUNCTION__);
			Console::info(__CLASS__ . ':beforeUninstall');
			$this->beforeUninstall();
			Console::info(__CLASS__ . ':afterUninstall');
			$this->afterUninstall();
			Console::timeEnd(__CLASS__ . ':' . __FUNCTION__);

		}

		final public function install()
		{
			Console::time(__CLASS__ . ':' . __FUNCTION__);
			Console::info(__CLASS__ . ':beforeInstall');
			$this->beforeInstall();

			$packages = self::getComposerPackage();
			if (!empty($packages)) {
				$packages = implode(' ', $packages);
				self::composerRequire($packages);
			}

			Console::info(__CLASS__ . ':afterInstall');
			$this->afterInstall();
			Console::timeEnd(__CLASS__ . ':' . __FUNCTION__);
		}

		final public static function composerRequire(string $package)
		{
			return Composer::require($package);
		}

	}