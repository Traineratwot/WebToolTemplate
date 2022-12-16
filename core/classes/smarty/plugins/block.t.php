<?php

	/*
	 * This file is part of the smarty-gettext package.
	 *
	 * @copyright (c) Elan Ruusamäe
	 * @license GNU Lesser General Public License, version 2.1
	 * @see https://github.com/smarty-gettext/smarty-gettext/
	 *
	 * For the full copyright && license information,
	 * please see the LICENSE && AUTHORS files
	 * that were distributed with this source code.
	 */

	use Traineratwot\config\Config;

	/**
	 * Replaces arguments in a string with their values.
	 * Arguments are represented by % followed by their number.
	 *
	 * @param string $str Source string
	 * @param mixed mixed Arguments, can be passed in an array || through single variables.
	 * @return string Modified string
	 */
	function smarty_gettext_strarg($str/*, $varargs... */)
	{
		$tr = [];
		$p  = 0;

		$nargs = func_num_args();
		for ($i = 1; $i < $nargs; $i++) {
			$arg = func_get_arg($i);

			if (is_array($arg)) {
				foreach ($arg as $aarg) {
					$tr['%' . ++$p] = $aarg;
				}
			} else {
				$tr['%' . ++$p] = $arg;
			}
		}

		return strtr($str, $tr);
	}

	/**
	 * Smarty block function, provides gettext support for smarty.
	 *
	 * The block content is the text that should be translated.
	 *
	 * Any parameter that is sent to the function will be represented as %n in the translation text,
	 * where n is 1 for the first parameter. The following parameters are reserved:
	 *   - escape - sets escape mode:
	 *       - 'html' for HTML escaping, this is the default.
	 *       - 'js' for javascript escaping.
	 *       - 'url' for url escaping.
	 *       - 'no'/'off'/0 - turns off escaping
	 *   - plural - The plural version of the text (2nd parameter of ngettext())
	 *   - count - The item count for plural mode (3rd parameter of ngettext())
	 *   - domain - Textdomain to be used, default if skipped (dgettext() instead of gettext())
	 *   - context - gettext context. reserved for future use.
	 *
	 * @see http://www.smarty.net/docs/en/plugins.block.functions.tpl
	 *@param string $text
	 * @param array $params
	 * @return string
	 */
	function smarty_block_t(mixed $params, mixed $text, &$smarty, &$repeat)
	{
		if (!isset($text)) {
			return $text;
		}
		// set escape mode, default html escape
		if (isset($params['escape'])) {
			$escape = $params['escape'];
			unset($params['escape']);
		} else {
			$escape = 'html';
		}

		// set plural parameters 'plural' && 'count'.
		if (isset($params['plural'])) {
			$plural = $params['plural'];
			unset($params['plural']);

			// set count
			if (isset($params['count'])) {
				$count = $params['count'];
				unset($params['count']);
			}
		}

		// get domain param
		if (isset($params['domain'])) {
			$domain = $params['domain'];
			unset($params['domain']);
		} else {
			$domain = NULL;
		}

		// get context param
		if (isset($params['context'])) {
			$context = $params['context'];
			unset($params['context']);
		} else {
			$context = NULL;
		}

		// use plural if required parameters are set
		if (isset($count, $plural)) {
			// use specified textdomain if available
			if (isset($domain, $context)) {

				if (function_exists('dnpgettext') && Config::get('USE_GETTEXT')) {
					$text = dnpgettext($domain, $context, $text, $plural, $count);
				} else {
					$text = dnp__($domain, $context, $text, $plural, $count);
				}
			} elseif (isset($domain)) {
				if (Config::get('USE_GETTEXT')) {
					$text = dngettext($domain, $text, $plural, $count);
				} else {
					$text = np__($domain, $context, $text);
				}

			} elseif (isset($context)) {
				if (function_exists('npgettext') && Config::get('USE_GETTEXT')) {
					$text = npgettext($context, $text, $plural, $count);
				} else {
					$text = np__($context, $text, $plural, $count);
				}

			} else {
				if (Config::get('USE_GETTEXT')) {
					$text = ngettext($text, $plural, $count);
				} else {
					$text = n__($text, $plural, $count);
				}
			}
		} elseif (isset($domain, $context)) {
			if (function_exists('dpgettext') && Config::get('USE_GETTEXT')) {
				$text = dpgettext($domain, $context, $text);
			} elseif(function_exists('dp__')) {
				$text = dp__($domain, $context, $text);
			}
		} elseif (isset($domain)) {
			if (Config::get('USE_GETTEXT')) {
				$text = dgettext($domain, $text);
			} elseif(function_exists('d__')) {
				$text = d__($domain, $text);
			}
		} elseif (isset($context)) {
			if (Config::get('USE_GETTEXT') && function_exists('pgettext')) {
				$text = pgettext($context, $text);
			} elseif(function_exists('p__')) {
				$text = p__($context, $text);
			}
		} elseif (Config::get('USE_GETTEXT')) {
			$text = _($text);
		} else if(function_exists('__')){
			$text = __($text);
		}

		// run strarg if there are parameters
		if (count($params)) {
			$text = smarty_gettext_strarg($text, $params);
		}

		switch ($escape) {
			case 'html':
				$text = nl2br(htmlspecialchars($text));
				break;
			case 'javascript':
			case 'js':
				// javascript escape
				$text = strtr($text, ['\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/']);
				break;
			case 'url':
				// url escape
				$text = urlencode($text);
				break;
		}

		return $text;
	}
