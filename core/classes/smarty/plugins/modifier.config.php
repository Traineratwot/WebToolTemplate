<?php
	/*
	 * Smarty plugin
	 * -------------------------------------------------------------
	 * Файл:     modifier.config.php
	 * Тип:     modifier
	 * Имя:     config
	 * Назначение:  Сделать первую букву каждого слова в
	 * строке прописной
	 * -------------------------------------------------------------
	 */
	function smarty_modifier_config($text = '', $arr = [])
	{
		if (defined($text)) {
			return constant($text);
		}

		if (!empty($arr) && isset($arr[$text])) {
			return $arr[$text];
		}

		return NULL;
	}

