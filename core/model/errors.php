<?php

	namespace core\model;

	use Exception;

	class Err
	{
		const template = "[+lvl+] [+datetime+] - +message+ (+file+:+line+)\n";

		public static function pretty($data = [])
		{
			foreach ($data as $key => $v) {
				$v = is_array($v) ? json_encode($v, 256) : $v;
				$v = is_resource($v) ? 'resource id: ' . get_resource_id($v) : $v;
				$replace_pairs['+' . $key . '+'] = $v;
			}
			return strtr(Err::template, $replace_pairs);

		}

		public static function save($str)
		{
			file_put_contents(WT_CACHE_PATH . 'error.log', $str, FILE_APPEND);
		}

		public static function err($msg, $line = __LINE__, $file = __FILE__)
		{
			Err::error($msg, $line, $file);
		}

		public static function error($msg, $line = __LINE__, $file = __FILE__)
		{
			Err::save(Err::pretty([
				'lvl' => 'error',
				'datetime' => date('Y-m-d H:i:s'),
				'message' => $msg,
				'file' => basename($file) ?: NULL,
				'line' => $line ?: NULL,
			]));
		}

		public static function warning($msg, $line = __LINE__, $file = __FILE__)
		{
			Err::save(Err::pretty([
				'lvl' => 'warning',
				'datetime' => date('Y-m-d H:i:s'),
				'message' => $msg,
				'file' => basename($file) ?: NULL,
				'line' => $line ?: NULL,
			]));
		}

		public static function fatal($msg, $line = __LINE__, $file = __FILE__)
		{
			Err::save(Err::pretty([
				'lvl' => 'error',
				'datetime' => date('Y-m-d H:i:s'),
				'message' => $msg,
				'file' => basename($file) ?: NULL,
				'line' => $line ?: NULL,
			]));
			throw new Exception($msg);
		}

		public static function info($msg, $line = __LINE__, $file = __FILE__)
		{
			Err::save(Err::pretty([
				'lvl' => 'info',
				'datetime' => date('Y-m-d H:i:s'),
				'message' => $msg,
				'file' => basename($file) ?: NULL,
				'line' => $line ?: NULL,
			]));
		}
	}