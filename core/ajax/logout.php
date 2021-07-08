<?php

	namespace core\ajax;
	/** @var Core $core */

	util::setCookie('authKey', NULL);
	die(util::success('Ok'));