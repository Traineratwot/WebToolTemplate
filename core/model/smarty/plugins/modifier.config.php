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
	$WT_GETTEXT = NULL;
	function smarty_modifier_config($text='', $arr = [])
	{
		if (defined($text)) {
			return constant($text);
		} elseif (!empty($arr) and isset($arr[$text])) {
			return $arr[$text];
		} else {
			return NULL;
		}
	}

?>