<?php

	namespace ajax;

	use core\Err;
	use core\Core;
	use core\util;

	/** @var Core $core */

	include_once '../core/engine.php';
	$email = strip_tags($_REQUEST['email']);
	$password = strip_tags($_REQUEST['password']);
	try {
		if ($email and $password) {
			$User = $core->getUser(['email' => $email]);
			if (!$User->isNew()) {
				if ($User->get('password') !== md5($password)) {
					Err::fatal('Wrong password',__FILE__,__FILE__);
				} else {
					util::setCookie('authKey', $User->get('authKey'));
					die(util::success('Ok'));
				}
			} else {
				Err::fatal('User not exists',__FILE__,__FILE__);
			}
		}
	} catch (\Exception $e) {
		die(util::failure($e->getMessage()));
	}