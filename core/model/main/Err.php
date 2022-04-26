<?php

	namespace model\main;

	use RuntimeException;

	class Err
	{
		public const template = "[+lvl+] [+datetime+] - +message+ (+file+:+line+)\n";

		public static function err($msg, $line = NULL, $file = NULL)
		{
			$d    = self::getTrace(__FUNCTION__);
			$line = $line ?: $d['line'];
			$file = $file ?: $d['file'];
			self::error($msg, $line, $file);
		}

		public static function error($msg, $line = NULL, $file = NULL)
		{
			$d    = self::getTrace(__FUNCTION__);
			$line = $line ?: $d['line'];
			$file = $file ?: $d['file'];
			self::save(self::pretty([
										'lvl'      => 'error',
										'datetime' => date('Y-m-d H:i:s'),
										'message'  => $msg,
										'file'     => basename($file) ?: NULL,
										'line'     => $line ?: NULL,
									]));
		}

		public static function warning($msg, $line = NULL, $file = NULL)
		{
			$d    = self::getTrace(__FUNCTION__);
			$line = $line ?: $d['line'];
			$file = $file ?: $d['file'];
			self::save(self::pretty([
										'lvl'      => 'warning',
										'datetime' => date('Y-m-d H:i:s'),
										'message'  => $msg,
										'file'     => basename($file) ?: NULL,
										'line'     => $line ?: NULL,
									]));
		}

		public static function fatal($msg, $line = NULL, $file = NULL)
		{
			$d    = self::getTrace(__FUNCTION__);
			$line = $line ?: $d['line'];
			$file = $file ?: $d['file'];
			self::save(self::pretty([
										'lvl'      => 'error',
										'datetime' => date('Y-m-d H:i:s'),
										'message'  => $msg,
										'file'     => basename($file) ?: NULL,
										'line'     => $line ?: NULL,
									]));
			throw new RuntimeException($msg);
		}

		public static function info($msg, $line = NULL, $file = NULL)
		{
			self::save(self::pretty([
										'lvl'      => 'info',
										'datetime' => date('Y-m-d H:i:s'),
										'message'  => $msg,
										'file'     => basename($file) ?: NULL,
										'line'     => $line ?: NULL,
									]));
		}

		public static function save($str)
		{
			if (!is_dir(WT_CACHE_PATH) && !mkdir($concurrentDirectory = WT_CACHE_PATH, 0777, 1) && !is_dir($concurrentDirectory)) {
				throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
			}
			file_put_contents(WT_CACHE_PATH . 'error.log', $str . PHP_EOL, FILE_APPEND);
		}

		public static function getTrace($fn)
		{
			$debug = debug_backtrace();
			foreach ($debug as $d) {
				if ($d['function'] === $fn) {
					return $d;
				}
			}
			return [];
		}

		public static function pretty($data = [])
		{
			$replace_pairs = [
				"\n" => " ",
				"\r" => " ",
			];
			foreach ($data as $key => $v) {
				$v = is_array($v) ? json_encode($v, 256) : $v;
				if (is_resource($v) && function_exists('get_resource_id')) {
					$v = 'resource id: ' . get_resource_id($v);
				}
				$replace_pairs['+' . $key . '+'] = $v;
			}
			return strtr(self::template, $replace_pairs);

		}
	}