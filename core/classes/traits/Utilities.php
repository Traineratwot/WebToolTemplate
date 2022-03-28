<?php

	namespace traits;

	use Exception;
	use model\main\Cache;

	/**
	 * Класс с утилитами
	 */
	trait Utilities
	{
		public static function ping($host = '', $useSocket = FALSE, $timeout = 2, $port = 80)
		{
			$_args = func_get_args();
			if (count($_args) == 1 and is_array($_args[0])) {
				extract($_args[0], EXTR_OVERWRITE);
			}
			if ($host) {
				$sock = FALSE;
				if ($useSocket) {
					$sock = @fsockopen($host, $port, $errno, $errStr, $timeout);
				}
				if (!$sock) {
					if (!$useSocket or $errStr == 'Unable to find the socket transport "https" - did you forget to enable it when you configured PHP?') {
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
						if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
							$context = stream_context_create($opts);
							$headers = @get_headers($host, 1, $context); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
						} else {
							stream_context_set_default($opts);
							$headers = @get_headers($host, 1); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
						}
						preg_match('@HTTP\/\d+.\d+\s([2-3]\d+)?\s@', $headers[0], $math);
						if (isset($math[1]) and $math[1]) {
							return TRUE;
						} else {
							return FALSE;
						}
					}
				} else {
					return TRUE;
				}
			}
			return FALSE;
		}

		public function success($msg, $object = [])
		{
			return json_encode([
								   'success' => TRUE,
								   'message' => $msg,
								   'object'  => $object,
							   ], 256);

		}

		public function failure($msg, $object = [])
		{
			return json_encode([
								   'success' => FALSE,
								   'message' => $msg,
								   'object'  => $object,
							   ], 256);

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
			$sys = \model\util::rawText(php_uname('s'));
			if (strpos($sys, 'windows') !== FALSE) {
				return 'win';
			}
			if (strpos($sys, 'linux') !== FALSE) {
				return 'nix';
			}
			return 'nix';
		}

		public static function rawText($a = '')
		{
			return mb_strtolower(preg_replace('@[^A-zА-я0-9]|[\/_\\\.\,]@u', '', (string)$a));
		}

		public static function baseExt($file = '')
		{
			$_tmp = explode('.', basename($file));
			return end($_tmp);
		}

		/**
		 * @param string $file
		 * @return string
		 */
		public static function baseName($file = '')
		{
			$_tmp = explode('.', basename($file));
			array_pop($_tmp);
			return implode('', $_tmp);
		}

		public static function getRequestHeaders()
		{
			if (function_exists('getallheaders')) {
				return getallheaders();
			} else {
				if (!is_array($_SERVER)) {
					return [];
				}
				$headers = [];
				foreach ($_SERVER as $name => $value) {
					if (substr($name, 0, 5) == 'HTTP_') {
						$key           = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
						$headers[$key] = $value;
					}
				}
				return $headers;
			}
			return [];
		}

		public static function headerJson()
		{
			@header("Content-type: application/json; charset=utf8");
		}

		public static function getSetOption($table = '', $column = '')
		{
			if (empty($table) or empty($column)) {
				return FALSE;
			}
			$core = Core::init();
			if (!($ret = $core->db->query("SHOW COLUMNS FROM `$table` LIKE '$column'"))) {
				return FALSE;
			}
			$line = $ret->fetch(PDO::FETCH_ASSOC);
			$set  = rtrim(ltrim(preg_replace('@^[setnum]+@', '', $line['Type']), "('"), "')");
			return preg_split("/','/", $set);
		}

		/**
		 * Recursive `glob()`.
		 * @param string $baseDir Base directory to search
		 * @param string $pattern Glob pattern
		 * @param int    $flags   Behavior bitmask
		 * @return array|string|bool
		 */
		public static function glob(string $baseDir, string $pattern, int $flags = GLOB_NOSORT | GLOB_BRACE)
		{
			$dirs       = new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS);
			$fileList   = [];
			$fileList[] = glob($baseDir . DIRECTORY_SEPARATOR . $pattern, $flags);
			foreach ($dirs as $dir) {
				$fileList[] = glob(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pattern, $flags);
			}
			$fileList = array_unique(array_merge(...$fileList));
			foreach ($fileList as $k => $file) {
				$fileList[$k] = realpath($file);
			}
			return $fileList;
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
			if (!file_exists($path) or !is_dir($path)) {
				if (!mkdir($path, 0777, TRUE) && !is_dir($path)) {
					throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
				}
			}
			return $path;
		}

		public static function hash($data)
		{
			if (!is_string($data)) {
				$data = serialize($data);
			}
			return hash('sha256', $data);
		}

		public static function pathNormalize($path)
		{
			$path = strtr($path, [
				'/'  => "/",
				'\\' => "/",
			]);
			if (file_exists($path)) {
				if (is_dir($path)) {
					if (WT_TYPE_SYSTEM === 'nix') {
						$path = "/" . trim($path, "/") . "/";
					} else {
						$path = trim($path, "/") . "/";
					}
				} else {
					if (WT_TYPE_SYSTEM === 'nix') {
						$path = "/" . trim($path, "/");
					} else {
						$path = trim($path, "/");
					}
				}
				return $path;
			}
			return $path;
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
			$path = self::pathNormalize($path);
			if (file_exists($path)) {
				return realpath($path);
			}
			return Cache::call(
				[$path],
				function ($path) {
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
						$dir = scandir($p);
						foreach ($dir as $d) {
							if (mb_strtolower($d) == mb_strtolower(basename($array_path[$k]))) {
								$s--;
								$p .= '/' . $d;
								break;
							}
						}
						if ($k == 0) {
							break;
						}
					}
					//проверяю что путь найден путем сравнения количество необходимых с количеством найденный частей путя
					if ($s === $k and $k === 0) {
						if (file_exists($p)) {
							return realpath($p);
						}
					}
					return NULL;
				},
				600,
				'filePaths',
				$path
			);
		}
	}