<?php

	namespace model\components;

	use core\model\composer\Composer;
	use model\main\CoreObject;
	use ReflectionClass;
	use Traineratwot\Cache\Cache;
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
		abstract public static function getTables()
		: array;

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
			unlink($this->getPath() . '/isInstall.lock');
		}

		public function afterInstall()
		{
			file_put_contents($this->getPath() . '/isInstall.lock', time());
		}

		public function isInstalled()
		{
			return file_exists($this->getPath() . '/isInstall.lock');
		}

		//end check install


		final public function uninstall()
		{
			Console::time(get_class($this) . ':' . __FUNCTION__);
			Console::info(get_class($this) . ':beforeUninstall');
			$this->beforeUninstall();
			Console::info(get_class($this) . ':afterUninstall');
			$this->afterUninstall();
			Console::timeEnd(get_class($this) . ':' . __FUNCTION__);

		}

		final public function install()
		{
			if ($this->isInstalled()) {
				return FALSE;
			}
			Console::time(get_class($this) . ':' . __FUNCTION__);
			Console::info(get_class($this) . ':beforeInstall');
			$this->beforeInstall();
			/** @var Manifest $cls */
			$cls      = get_class($this);
			$packages = $cls::getComposerPackage();
			if (!empty($packages)) {
				$packages = implode(' ', $packages);
				self::composerRequire($packages);
			}
			$tables = $cls::getTables();
			foreach ($tables as $table) {
				if (class_exists($table)) {
					$tb = new$table($this->core);
					if ($tb instanceof ComponentTable) {
						$tb->createTable();
						Console::info(get_class($this) . ':installed table: ' . $table);
					}
				}
			}
			Console::info(get_class($this) . ':afterInstall');
			$this->afterInstall();
			Cache::removeAll();
			Console::timeEnd(get_class($this) . ':' . __FUNCTION__);
		}

		final public static function composerRequire(string $package)
		{
			return Composer::require($package);
		}

		final public function getPath()
		{
			return dirname((new ReflectionClass(get_class($this)))->getFileName());
		}
	}