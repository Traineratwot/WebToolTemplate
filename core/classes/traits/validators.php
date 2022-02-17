<?php

	namespace classes\traits;

	use DOMDocument;
	use DOMXPath;
	use Exception;

	class ExceptionValidate extends Exception
	{
	}

	trait htmlValidate
	{
		public function htmlValidate($string)
		{
			if (is_string($string)) {
				$document = new DOMDocument();
				$document->loadHTML($string);
				$xpath = new DOMXpath($document);
				$a     = $xpath->query('//script');
				if ($a->length) {
					throw new ExceptionValidate(__('danger script detected') . ': ' . '"script"');
				}
				$a = $xpath->query('//link');
				if ($a->length) {
					throw new ExceptionValidate(__('danger script detected') . ': ' . '"link"');
				}
				$a = $xpath->query('//iframe');
				if ($a->length) {
					throw new ExceptionValidate(__('danger script detected') . ': ' . '"iframe"');
				}
				$a = $xpath->query('//embed');
				if ($a->length) {
					throw new ExceptionValidate(__('danger script detected') . ': ' . '"embed"');
				}
			}
			return $string;
		}
	}

	trait emptyValidate
	{
		public function emptyValidate($value, $strict = FALSE)
		{
			if (empty($value)) {
				throw new ExceptionValidate(__('can`t be') . ' ' . __('empty'));
			} elseif ($strict) {
				if (!(bool)$value) {
					throw new ExceptionValidate(__('can`t be') . ' ' . $value);
				}
			}
			return $value;
		}
	}

	trait emailValidate
	{
		public function emailValidate($value)
		{
			$value = trip_tags($value);
			if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
				throw new ExceptionValidate('invalid email');
			}
			return $value;
		}
	}

	trait passValidate
	{
		public function passValidate($value, $length = 6)
		{
			$value = trip_tags($value);
			if (preg_match('/^.*(?=.{' . $length . ',})(?=.*[a-zA-Z])(?=.*\d)(?=.*[!#$%&? "]).*$/')) {
				throw new ExceptionValidate('invalid password');
			}
			return $value;
		}
	}

	trait imgValidate
	{
		public function imgValidate($url, $maxW = 0, $maxH = 0, $minW = 0, $minH = 0)
		{
			$info = getimagesize($url);
			if (!$info) {
				throw new ExceptionValidate('is not image');
			}
			$width  = $info[0];
			$height = $info[1];
			if ($maxW) {
				if (!$maxH) {
					$maxH = $maxW;
				}
				if ($width > $maxW or $height > $maxH) {
					throw new ExceptionValidate('image too big');
				}
			}
			if ($minW) {
				if (!$minH) {
					$minH = $minW;
				}
				if ($width < $minW or $height < $minH) {
					throw new ExceptionValidate('image too small');
				}
			}
			return $url;
		}
	}

	trait jsonValidate
	{
		public function jsonValidate($string, $assoc = TRUE, $depth = 1024)
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

				// switch and check possible JSON errors
				switch (json_last_error()) {
					case JSON_ERROR_NONE:
						$error = 0; // JSON is valid // No error has occurred
						break;
					case JSON_ERROR_DEPTH:
						$error = 'The maximum stack depth has been exceeded.';
						break;
					case JSON_ERROR_STATE_MISMATCH:
						$error = 'Invalid or malformed JSON.';
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
						$error = 'One or more recursive references in the value to be encoded.';
						break;
					// PHP >= 5.5.0
					case JSON_ERROR_INF_OR_NAN:
						$error = 'One or more NAN or INF values in the value to be encoded.';
						break;
					case JSON_ERROR_UNSUPPORTED_TYPE:
						$error = 'A value of a type that cannot be encoded was given.';
						break;
					default:
						$error = 'Unknown JSON error occurred.';
						break;
				}
				if ($error) {
					throw new ExceptionValidate($error);
				}
			} catch (Exception $e) {
				throw new ExceptionValidate($e->getMessage(), $e->getCode(), $e);
			}
			return $result;
		}
	}