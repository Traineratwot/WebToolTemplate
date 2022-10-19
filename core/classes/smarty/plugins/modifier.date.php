<?php
	function smarty_modifier_date(string $format, $date)
	: string
	{
		if ($date instanceof DateTime) {
			if (class_exists('IntlDateFormatter')) {
				$intlFormatter = new IntlDateFormatter('ru_RU', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
				$intlFormatter->setPattern($format);
				return $intlFormatter->format($date);
			}
			return $date->format($format);
		}
		return '';
	}

