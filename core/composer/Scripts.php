<?php

	namespace core\composer;

	use Exception;
	use model\main\Core;
	use model\main\Utilities;
	use PhpZip\Exception\ZipException;
	use PhpZip\ZipFile;
	use Traineratwot\Cache\Cache;
	use Traineratwot\PhpCli\Console;

	class Scripts
	{
		public static function postInstall()
		{
			require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

			$config = get_defined_constants();
			Console::info('Create missing directory');
			foreach ($config as $v => $i) {
				if ((stripos($v, 'WT_') === 0) && stripos($v, 'PATH') !== FALSE) {
					self::mkDirs($i);
				}
			}
			if (PHP_OS === "Linux") {
				Console::info('Change permission');
				exec('chmod 755 -R -f ' . WT_MODEL_PATH);
				exec('chmod 744 -R -f ' . WT_CORE_PATH . 'config.php');
				exec('chmod 744 -R -f ' . WT_CORE_PATH . 'config.json');
				exec('chmod 744 -R -f ' . WT_AJAX_PATH);
			}
			try {
				$core = Core::init();
				Cache::removeAll();
				$needInstall = !($core->db->tableExists("users"));
				if ($needInstall) {
					Console::info('Install Database');
					if ($core->db->dsn->getDriver() === 'sqlite') {
						if (!file_exists(WT_HOST_DB)) {
							file_put_contents(WT_HOST_DB, '');
						}
						$core->db->exec(<<<SQL
CREATE TABLE users
(
	id       INTEGER
		PRIMARY KEY AUTOINCREMENT,
	email    VARCHAR(64)
		UNIQUE
			ON CONFLICT FAIL,
	password VARCHAR(64),
	authKey  VARCHAR(64),
	salt     VARCHAR(64) DEFAULT 0
);
SQL
						);
					} else {
						$core->db->exec(<<<SQL
CREATE TABLE `users` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`password` VARCHAR(256) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`authKey` VARCHAR(256) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`salt` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`time_create` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `email` (`email`) USING BTREE,
	UNIQUE INDEX `authKey` (`authKey`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0
;
SQL
						);
					}
				}

			} catch (Exception $e) {
				Console::failure($e->getMessage());
			}
			Console::info('npm install');
			chdir(WT_BASE_PATH);
			exec('npm install');
			return TRUE;
		}

		public static function engineUpdate()
		{

		}

		/**
		 * @throws ZipException
		 */
		public static function package()
		{
			require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
			self::mkDirs(WT_BASE_PATH . '/backups/');
			$zipFile = new ZipFile();
			$zipFile->addDirRecursive(WT_BASE_PATH);
			$zipFile->deleteFromRegex("@.idea@");
			$zipFile->deleteFromRegex("@.vscode@");
			$zipFile->deleteFromRegex("@.git/objects@");
			$zipFile->deleteFromRegex("@node_modules@");
			$zipFile->deleteFromRegex("@backups@");
			$zipFile->saveAsFile(WT_BASE_PATH . '/backups/backup.zip');   // save the archive to a file
			$zipFile->close();                                            // close archive
		}

		public static function mkDirs(string $dir)
		{
			$dir = Utilities::pathNormalize($dir);
			if (!file_exists($dir)) {
				if (!mkdir($dir, 0777, 1) || !is_dir($dir) || !file_exists($dir)) {
					Console::failure(sprintf('Directory "%s" was not created', $dir));
				} else {
					Console::success(sprintf('Created "%s"', $dir));
				}
			}
		}
	}