<?php
	/**
	 * Created by Andrey Stepanenko.
	 * User: webnitros
	 * Date: 017, 17.02.2022
	 * Time: 22:46
	 */

	namespace traits\validators;

	use function __;

	trait htmlValidate
	{
		public static function htmlValidate($string)
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