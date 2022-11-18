<?php

	namespace core\model\composer;

	use DateTime;
	use Exception;
	use model\main\Utilities;
	use PhpZip\Exception\ZipException;
	use PhpZip\ZipFile;
	use tables\Users;
	use Traineratwot\config\Config;
	use Traineratwot\PDOExtended\Dsn;
	use Traineratwot\PDOExtended\PDOE;
	use Traineratwot\PhpCli\Console;

	class Scripts
	{
		public static function postInstall()
		: void
		{
			require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
			$config = get_defined_constants();
			Console::info('Create missing directory');
			foreach ($config as $v => $i) {
				if ((stripos($v, 'WT_') === 0) && stripos($v, 'PATH') !== FALSE) {
					self::mkDirs($i);
				}
			}
			if (PHP_OS === "Linux" && self::commandExist('exec')) {
				Console::info('Change permission');
				exec('chmod 755 -R -f ' . Config::get('MODEL_PATH'));
				exec('chmod 744 -R -f ' . Config::get('CORE_PATH') . 'config.php');
				exec('chmod 744 -R -f ' . Config::get('CORE_PATH') . 'config.json');
				exec('chmod 744 -R -f ' . Config::get('AJAX_PATH'));
			}
			try {
				$dsn = new Dsn();
				$dsn->setDriver(Config::get('TYPE_DB'));
				$dsn->setDatabase(Config::get('DATABASE_DB'));
				if (Config::get('CHARSET_DB')) {
					$dsn->setCharset(Config::get('CHARSET_DB'));
				}
				if (Config::get('HOST_DB')) {
					$dsn->setHost(Config::get('HOST_DB'));
				} else {
					$dsn->setSocket(Config::get('SOCKET_DB'));
				}
				$dsn->setPort((int)Config::get('PORT_DB'));
				$dsn->setPassword(Config::get('PASS_DB'));
				$dsn->setUsername(Config::get('USER_DB'));
				$db          = PDOE::init($dsn);
				$needInstall = !($db->tableExists("users"));
				if ($needInstall) {
					$t = Users::createTable();
					if ($t) {
						Console::info('install `users` table');
						$db->exec($t->setDriver($db->getDriver())->toSql());
					}
				}

			} catch (Exception $e) {
				Console::failure($e->getMessage());
			}
			if (self::commandExist('npm')) {
				Console::info('npm install');
				chdir(Config::get('BASE_PATH'));
				exec('npm install');
			} else {
				Console::warning('Please install npm');
			}
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
		 * @throws Exception
		 */
		public static function engineUpdate()
		: void
		{
			require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
			Console::info("make backup");
			self::package();
			self::rmdir(Config::get('BASE_PATH') . 'update/');
			self::mkDirs(Config::get('BASE_PATH') . 'update/');
			$dist = Config::get('BASE_PATH') . 'update/';
			Console::info("Download new version");
			file_put_contents($dist . 'master.zip', file_get_contents("https://github.com/Traineratwot/WebToolTemplate/archive/refs/heads/master.zip"));
			$zipFile = new ZipFile();
			$zipFile->openFile($dist . 'master.zip');
			$zipFile->extractTo($dist);
			$zipFile->close();
			self::copy(Utilities::findPath(Config::get('BASE_PATH') . 'update/WebToolTemplate-master/core/model'), Config::get('MODEL_PATH'));
			//TODO:merge composer json and package.json

			self::rmdir(Config::get('BASE_PATH') . 'update/');
		}

		/**
		 * @throws ZipException
		 */
		public static function package()
		: void
		{
			//------------------------------DATABASE-------------------------------------
			$dt = new DateTime();
			require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
			self::mkDirs(Config::get('BASE_PATH') . '/backups/');
			$zipFile = new ZipFile();
			$zipFile->setArchiveComment(<<<HTML
Backup DataBase
Date: {$dt->format(DATE_ATOM)}
HTML
			);
			switch (Config::get('TYPE_DB')) {
				case PDOE::DRIVER_MySQL:
					if (self::commandExist('mysqldump')) {
						$h = Config::get('HOST_DB');
						$p = Config::get('PASS_DB');
						$u = Config::get('USER_DB');
						$d = Config::get('DATABASE_DB');
						$s = Config::get('CORE_PATH') . 'database/dump.sql';
						self::mkDirs(dirname($s));
						$cmd = <<<HTML
mysqldump -u $u -p$p -h $h $d > "$s"
HTML;
						exec($cmd, $o, $c);
						if ($c === 0) {
							$zipFile->addFile($s);

							$zipFile->saveAsFile(Utilities::pathNormalize(Config::get('BASE_PATH') . '/backups/dump_' . $dt->format("Y-m-d H-i") . '_.zip'));
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
					$zipFile->addFile(Config::get('HOST_DB'));
					$zipFile->saveAsFile(Utilities::pathNormalize(Config::get('BASE_PATH') . '/backups/dump_' . $dt->format("Y-m-d H-i") . '_.zip'));
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
			$zipFile->addDirRecursive(Config::get('BASE_PATH'));
			$zipFile->deleteFromRegex("@.idea@");
			$zipFile->deleteFromRegex("@.vscode@");
			$zipFile->deleteFromRegex("@.git@");
			$zipFile->deleteFromRegex("@.project-manager@");
			$zipFile->deleteFromRegex("@node_modules@");
			$zipFile->deleteFromRegex("@backups@");
			$zipFile->deleteFromRegex("@packages@");
			$zipFile->deleteFromRegex("@update@");
			$zipFile->saveAsFile(Utilities::pathNormalize(Config::get('BASE_PATH') . '/backups/backup_' . $dt->format("Y-m-d H-i") . '_.zip'));
			$zipFile->close();
		}

		private static function rmdir($dir)
		: void
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
		: void
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

		public static function test()
		: void
		{
			require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
			Console::info("Successfully loaded");
		}
	}