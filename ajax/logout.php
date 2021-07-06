<?php

	namespace ajax;

	use core\Err;
	use core\Core;
	use core\util;

	/** @var Core $core */

	include_once '../core/engine.php';
	util::setCookie('authKey', NULL);
	die(util::success('Ok'));