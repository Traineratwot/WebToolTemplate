<?php

	namespace traits\validators;

	trait ImgValidate
	{
		/**
		 * @throws ExceptionValidate
		 */
		public static function imgValidate($url, $maxW = 0, $maxH = 0, $minW = 0, $minH = 0)
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
				if ($width > $maxW || $height > $maxH) {
					throw new ExceptionValidate('image too big');
				}
			}
			if ($minW) {
				if (!$minH) {
					$minH = $minW;
				}
				if ($width < $minW || $height < $minH) {
					throw new ExceptionValidate('image too small');
				}
			}
			return $url;
		}
	}