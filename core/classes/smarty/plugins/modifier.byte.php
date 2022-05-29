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
	$Config::get('GETTEXT') = NULL;
	function smarty_modifier_byte($text = '', $arr = [])
	{
		$size = (int)$text;
		$i    = 0;
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
		}
		return $size;
	}

?>
