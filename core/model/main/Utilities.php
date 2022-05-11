<?php

	namespace model\main;

	use DateTime;
	use Exception;
	use FilesystemIterator;
	use PDO;
	use RecursiveDirectoryIterator;
	use RecursiveIteratorIterator;
	use RuntimeException;
	use SplFileInfo;
	use Traineratwot\Cache\Cache;
	use traits\validators\jsonValidate;
	use const WT_TYPE_SYSTEM;

	/**
	 * Класс с утилитами
	 */
	class Utilities
	{
		use JsonValidate;

		/**
		 * @param $host
		 * @param $useSocket
		 * @param $timeout
		 * @param $port
		 * @return bool
		 */
		public static function ping($host = '', $useSocket = FALSE, $timeout = 2, $port = 80)
		{
			$args = func_get_args();
			if (count($args) === 1 && is_array($args[0])) {
				extract($args[0], EXTR_OVERWRITE);
			}
			if ($host) {
				$sock = FALSE;
				if ($useSocket) {
					$sock = @fsockopen($host, $port, $errno, $errStr, $timeout);
				}
				if (!$sock) {
					if (!$useSocket || $errStr === 'Unable to find the socket transport "https" - did you forget to enable it when you configured PHP?') {
						$opts = [
							'http'  => [
								'timeout' => $timeout,
								'header'  => "User - Agent: Mozilla / 5.0\r\n",

							],
							'https' => [
								'timeout' => $timeout,
								'header'  => "User - Agent: Mozilla / 5.0\r\n",
							],
						];
						if (PHP_VERSION_ID >= 70100) {
							$context = stream_context_create($opts);
							$headers = @get_headers($host, 1, $context); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
						} else {
							stream_context_set_default($opts);
							$headers = @get_headers($host, 1); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
						}
						preg_match('@HTTP/\d+.\d+\s([2-3]\d+)?\s@', $headers[0], $math);
						return isset($math[1]) && $math[1];
					}
				} else {
					return TRUE;
				}
			}
			return FALSE;
		}

		public static function setCookie($name, $value, $time = 0)
		{
			$expire = $time ?: time() + 31556926;
			setcookie($name, $value, $expire, '/');
		}

		public static function id($length = 6)
		{
			$length--;
			$password = 'a';
			$arr      = [
				'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
				'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
				'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
				'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
				'1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
			];
			for ($i = 0; $i < $length; $i++) {
				$password .= $arr[random_int(0, count($arr) - 1)];
			}
			return $password;
		}

		public static function getIp()
		{
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			return filter_var($ip, FILTER_VALIDATE_IP) ? (string)$ip : FALSE;
		}

		public static function getSystem()
		{
			$sys = self::rawText(php_uname('s'));
			if (strpos($sys, 'windows') !== FALSE) {
				return 'win';
			}
			if (strpos($sys, 'linux') !== FALSE) {
				return 'nix';
			}
			return 'unknown';
		}

		public static function rawText($a = '')
		{
			return mb_strtolower(preg_replace('@[^A-zА-я\d]|[/_\\\.,]@u', '', (string)$a));
		}

		public static function baseExt($file = '')
		{
			$tmp = explode('.', basename($file));
			return end($tmp);
		}

		/**
		 * @param string $file
		 * @return string
		 */
		public static function baseName($file = '')
		{
			$tmp = explode('.', basename($file));
			array_pop($tmp);
			return implode('', $tmp);
		}

		public static function getRequestHeaders()
		{
			if (function_exists('getallheaders')) {
				return getallheaders();
			}
			if (!is_array($_SERVER)) {
				return [];
			}
			$headers = [];
			foreach ($_SERVER as $name => $value) {
				if (strpos($name, 'HTTP_') === 0) {
					$key           = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
					$headers[$key] = $value;
				}
			}
			return $headers;
		}

		public static function headerJson()
		{
			@header("Content-type: application/json; charset=utf8");
		}

		public static function getSetOption($table = '', $column = '')
		{
			if (empty($table) || empty($column)) {
				return FALSE;
			}
			$core = Core::init();
			if (!($ret = $core->db->query("SHOW COLUMNS FROM `$table` LIKE '$column'"))) {
				return FALSE;
			}
			$line = $ret->fetch(PDO::FETCH_ASSOC);
			$set  = rtrim(ltrim(preg_replace('@^[setnum]+@', '', $line['Type']), "('"), "')");
			return explode("','", $set);
		}

		/**
		 * Recursive `glob()`.
		 * @param string $baseDir Base directory to search
		 * @param string $pattern Glob pattern
		 * @param int    $flags   Behavior bitmask
		 * @return array
		 */
		public static function glob(string $baseDir, string $pattern, int $flags = GLOB_NOSORT | GLOB_BRACE)
		{
			$IteratorDirs = new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS);
			$Iterator     = new RecursiveIteratorIterator($IteratorDirs);
			$fileList     = [];
			$fileList[]   = glob($baseDir . DIRECTORY_SEPARATOR . $pattern, $flags);
			/** @var SplFileInfo $dir */
			$dirs = [];
			foreach ($Iterator as $dir) {
				$dirs[] = dirname($dir->getPathname());
			}
			$dirs = array_unique($dirs);
			foreach ($dirs as $dir) {
				if (is_dir($dir)) {
					$fileList[] = glob(self::pathNormalize($dir) . $pattern, $flags);
				}
			}
			$fileList = array_unique(array_merge(...$fileList));
			foreach ($fileList as $k => $file) {
				$fileList[$k] = self::pathNormalize($file);
			}
			return $fileList;
		}

		public static function pathNormalize($path, $DIRECTORY_SEPARATOR = "/")
		{
			$path = preg_replace('/(\/+|\\\\+)/m', $DIRECTORY_SEPARATOR, $path);
			if (file_exists($path)) {
				if (is_dir($path)) {
					if (WT_TYPE_SYSTEM === 'nix') {
						$path = "/" . trim($path, $DIRECTORY_SEPARATOR) . $DIRECTORY_SEPARATOR;
					} else {
						$path = trim($path, $DIRECTORY_SEPARATOR) . $DIRECTORY_SEPARATOR;
					}
				} elseif (WT_TYPE_SYSTEM === 'nix') {
					$path = $DIRECTORY_SEPARATOR . trim($path, $DIRECTORY_SEPARATOR);
				} else {
					$path = trim($path, $DIRECTORY_SEPARATOR);
				}
				return $path;
			}
			return $path;
		}

		public static function arrayToSqlIn($arr = [])
		{
			$dop = array_fill(0, count($arr), 256);
			foreach ($arr as $key => $value) {
				$arr[$key] = trim($value, "'");
			}
			return @implode(',', array_map('json_encode', $arr, $dop));
		}

		public static function mkDirs($path)
		{
			if (!file_exists($path) || !is_dir($path)) {
				if (!mkdir($path, 0777, TRUE) && !is_dir($path)) {
					throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
				}
			}
			return $path;
		}

		public static function hash($data, $algo = 'sha256')
		{
			if (!is_string($data)) {
				$data = serialize($data);
			}
			return hash($algo, $data);
		}

		/**
		 * Находит файл независимо от регистра и возвращает его абсолютный
		 * путь в случаи неудаче вернет null
		 * @param $path
		 * @return string|null
		 * @throws Exception
		 */
		public static function findPath($path)
		{
			$path = self::pathNormalize($path, "/");
			if (file_exists($path)) {
				return realpath($path);
			}
			return Cache::call(
				[$path],
				function () use ($path) {
					//разбиваю путь на массив в котором каждый старший элемент родитель младшего
					$a          = explode("/", $path);
					$array_path = [];
					while (count($a)) {
						$array_path[] = implode("/", $a);
						unset($a[count($a) - 1]);
					}
					//нахожу первый существующий путь
					foreach ($array_path as $k => $p) {
						if (file_exists($p)) {
							break;
						}
					}
					$s = $k;
					//ищю следующую часть пути без учета регистра
					while ($k >= 0) {
						$k--;
						if (!empty($p)) {
							$dir = scandir($p);
							foreach ($dir as $d) {
								if (mb_strtolower($d) === mb_strtolower(basename($array_path[$k]))) {
									$s--;
									$p .= '/' . $d;
									break;
								}
							}
						}
						if ($k === 0) {
							break;
						}
					}
					//проверяю что путь найден путем сравнения количество необходимых с количеством найденный частей путя
					if (($s === $k && $k === 0) && file_exists($p)) {
						return realpath($p);
					}
					return NULL;
				},
				600,
				'filePaths');
		}

		public static function dateFormat($inputFormat, $date, $outputFormat = 'U', $modify = '')
		{
			$dt = DateTime::createFromFormat($inputFormat, $date);
			if ($dt) {
				if ($modify) {
					$dt->modify($modify);
				}
				if (!$outputFormat) {
					return $dt;
				}
				return $dt->format($outputFormat);
			}
			return FALSE;
		}

		public static function translit($value)
		{
			$converter = [
				'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
				'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
				'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
				'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
				'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
				'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
				'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

				'А'  => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
				'Е'  => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
				'Й'  => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
				'О'  => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
				'У'  => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch',
				'Ш'  => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
				'Э'  => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
				'\\' => "~", '/' => "~", ':' => "~", '*' => "~", '?' => "~", '"' => "~", '<' => "~", '>' => "~", '|' => "~", "'" => "~",
				' '  => "_",
			];

			return strtr($value, $converter);
		}

		public static function isAssoc(&$arr = [])
		{
			if (function_exists('array_is_list')) {
				return !array_is_list($arr);
			}

			if (is_array($arr)) {
				$c = count($arr);
				if ($c > 10) {
					return !(array_key_exists(0, $arr) && array_key_exists(random_int(0, $c - 1), $arr) && array_key_exists($c - 1, $arr));
				}

				if ($c > 0) {
					return !(range(0, count($arr) - 1) === array_keys($arr));
				}
			}
			return FALSE;
		}

		public static function convertBytes($size)
		{
			$i = 0;
			while (floor($size / 1024) > 0) {
				++$i;
				$size /= 1024;
			}

			$size = str_replace('.', ',', round($size, 1));
			switch ($i) {
				case 0:
					$size .= ' bytes';
					break;
				case 1:
					$size .= ' Kb';
					break;
				case 2:
					$size .= ' Mb';
					break;
				case 3:
					$size .= ' Gb';
					break;
				case 4:
					$size .= ' Tb';
					break;
			}
			return $size;
		}

		public static function success($msg, $object = [])
		{
			return json_encode([
								   'success' => TRUE,
								   'message' => $msg,
								   'object'  => $object,
							   ], 256);

		}

		public static function failure($msg, $object = [])
		{
			return json_encode([
								   'success' => FALSE,
								   'message' => $msg,
								   'object'  => $object,
							   ], 256);

		}

		public static function writeFile($path, $content)
		{
			if (!is_dir(dirname($path))) {
				$concurrentDirectory = dirname($path);
				if (!file_exists($concurrentDirectory) || !is_dir($concurrentDirectory)) {
					if (!mkdir($concurrentDirectory, 0777, 1) && !is_dir($concurrentDirectory)) {
						throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
					}
				}
			}
			file_put_contents($path, $content);
			return file_exists($path);
		}
	}