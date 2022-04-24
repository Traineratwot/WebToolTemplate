<?php

	namespace traits\validators;

	use Exception;

	trait jsonValidate
	{
		public static function jsonValidate($string, $assoc = TRUE, $depth = 1024)
		{
			try {
				if (!is_string($string)) {
					return $string;
				}
				$error = 0;
				// decode the JSON data
				$string = preg_replace('/[[:cntrl:]]/', '', $string);
				if (defined("JSON_THROW_ON_ERROR")) {
					$result = json_decode($string, (bool)$assoc, $depth, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
				} else {
					$result = json_decode($string, (bool)$assoc, $depth, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
				}

				// switch && check possible JSON errors
				switch (json_last_error()) {
					case JSON_ERROR_NONE:
						$error = 0; // JSON is valid // No error has occurred
						break;
					case JSON_ERROR_DEPTH:
						$error = 'The maximum stack depth has been exceeded.';
						break;
					case JSON_ERROR_STATE_MISMATCH:
						$error = 'Invalid || malformed JSON.';
						break;
					case JSON_ERROR_CTRL_CHAR:
						$error = 'Control character error, possibly incorrectly encoded.';
						break;
					case JSON_ERROR_SYNTAX:
						$error = 'Syntax error, malformed JSON.';
						break;
					// PHP >= 5.3.3
					case JSON_ERROR_UTF8:
						$error = 'Malformed utf8 characters, possibly incorrectly encoded.';
						break;
					// PHP >= 5.5.0
					case JSON_ERROR_RECURSION:
						$error = 'One || more recursive references in the value to be encoded.';
						break;
					// PHP >= 5.5.0
					case JSON_ERROR_INF_OR_NAN:
						$error = 'One || more NAN || INF values in the value to be encoded.';
						break;
					case JSON_ERROR_UNSUPPORTED_TYPE:
						$error = 'A value of a type that cannot be encoded was given.';
						break;
					default:
						$error = 'Unknown JSON error occurred.';
						break;
				}
				if ($error) {
					throw new Exception($error);
				}
			} catch (Exception $e) {
				throw new ExceptionValidate($e->getMessage(), $e->getCode(), $e);
			}
			return $result;
		}
	}