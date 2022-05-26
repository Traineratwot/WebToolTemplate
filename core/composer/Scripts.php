<?php

	namespace core\composer;

	use DateTime;
	use Exception;
	use model\main\Core;
	use model\main\Utilities;
	use PhpZip\Exception\ZipException;
	use PhpZip\ZipFile;
	use Traineratwot\Cache\Cache;
	use Traineratwot\PDOExtended\PDOE;
	use Traineratwot\PhpCli\Console;

	class Scripts
	{
		public static function postInstall()
		: void
		{
			require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
			$config = get_defined_constants();
			Console::info('Create missing directory');
			foreach ($config as $v => $i) {
				if ((stripos($v, 'WT_') === 0) && stripos($v, 'PATH') !== FALSE) {
					self::mkDirs($i);
				}
			}
			if (PHP_OS === "Linux" && self::commandExist('exec')) {
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
						$core->db->exec(<<<HTML
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
HTML
						);
					} else {
						$core->db->exec(<<<HTML
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
AUTO_INCREMENT=0;
HTML
						);
					}
				}

			} catch (Exception $e) {
				Console::failure($e->getMessage());
			}
			if (self::commandExist('npm')) {
				Console::info('npm install');
				chdir(WT_BASE_PATH);
				exec('npm install');
			} else {
				Console::warning('Please install npm');
			}
			die;
		}

		public static function engineUpdate()
		: void
		{
			require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
			self::mkDirs(WT_BASE_PATH . 'update');
			chdir(WT_BASE_PATH . 'update');
			exec('git clone "https://github.com/Traineratwot/WebToolTemplate.git" .');
			self::copy(WT_BASE_PATH . 'update/core/model', WT_MODEL_PATH);
			self::rmdir(WT_BASE_PATH . 'update/');
			die;

		}

		private static function commandExist($cmd)
		: bool
		{
			if (PHP_OS === "Linux") {
				$return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
				return !empty($return);
			}
			exec(sprintf("where %s", escapeshellarg($cmd)), $r, $c);
			if ($c !== 0) {
				return FALSE;
			}
			$r = shell_exec(sprintf("help %s", escapeshellarg($cmd)));
			return strpos('not supported', $r) === FALSE;
		}

		/**
		 * @throws ZipException
		 */
		public static function package()
		: void
		{
			//------------------------------DATABASE-------------------------------------
			$dt = new DateTime();
			require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
			self::mkDirs(WT_BASE_PATH . '/backups/');
			$zipFile = new ZipFile();
			$zipFile->setArchiveComment(<<<HTML
Backup DataBase
Date: {$dt->format(DATE_ATOM)}
HTML
			);
			switch (WT_TYPE_DB) {
				case PDOE::DRIVER_MySQL:
					if (self::commandExist('mysqldump')) {
						$h = WT_HOST_DB;
						$p = WT_PASS_DB;
						$u = WT_USER_DB;
						$d = WT_DATABASE_DB;
						$s = WT_CORE_PATH . 'database/dump.sql';
						self::mkDirs(dirname($s));
						$cmd = <<<HTML
mysqldump -u $u -p$p -h $h $d > "$s"
HTML;
						exec($cmd, $o, $c);
						if ($c === 0) {
							$zipFile->addFile($s);

							$zipFile->saveAsFile(WT_BASE_PATH . '/backups/dump.zip');
							$zipFile->close();
						} else {
							Console::warning("You must make dump, use: \"$cmd\"");
						}
					} else {
						Console::warning("failed to make a backup copy of the database");
					}
					break;
				case PDOE::DRIVER_PostgreSQL:

					break;
				case PDOE::DRIVER_SQLite:
					$zipFile->addFile(WT_HOST_DB);
					$zipFile->saveAsFile(WT_BASE_PATH . '/backups/dump.zip');
					$zipFile->close();
					break;
			}
			//---------------------------------CORE--------------------------------------
			$zipFile = new ZipFile();
			$zipFile->setArchiveComment(<<<HTML
Backup project
Date: {$dt->format(DATE_ATOM)}
HTML
			);
			$zipFile->addDirRecursive(WT_BASE_PATH);
			$zipFile->deleteFromRegex("@.idea@");
			$zipFile->deleteFromRegex("@.vscode@");
			$zipFile->deleteFromRegex("@.git@");
			$zipFile->deleteFromRegex("@node_modules@");
			$zipFile->deleteFromRegex("@backups@");
			$zipFile->saveAsFile(WT_BASE_PATH . '/backups/backup.zip');
			$zipFile->close();
			die;
		}

		private static function mkDirs(string $dir)
		: void
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

		private static function rmdir($dir)
		{
			if (is_dir($dir)) {
				$files = scandir($dir);
				foreach ($files as $file) {
					if ($file !== "." && $file !== "..") {
						self::rmdir("$dir/$file");
					}
				}
				rmdir($dir);
			} elseif (file_exists($dir)) {
				unlink($dir);
			}
		}

		private static function copy($src, $dst)
		{
			if (file_exists($dst)) {
				self::rmdir($dst);
			}
			if (is_dir($src)) {
				self::mkdirs($dst);
				$files = scandir($src);
				foreach ($files as $file) {
					if ($file !== "." && $file !== "..") {
						self::copy("$src/$file", "$dst/$file");
					}
				}
			} elseif (file_exists($src)) {
				copy($src, $dst);
			}
		}
	}