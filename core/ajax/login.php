<?php

	namespace core\ajax;
	use core\model\Core;
	use core\model\Err;
	use core\model\util;

	/** @var Core $core */
	$email = strip_tags($_REQUEST['email']);
	$password = strip_tags($_REQUEST['password']);
	try {
		if ($email and $password) {
			/** @var user $User */
			$User = $core->getUser(['email' => $email]);
			if (!$User->isNew()) {
				if ($User->get('password') !== md5($password)) {
					Err::fatal('Wrong password', __FILE__, __FILE__);
				} else {
					util::setCookie('authKey', $User->get('authKey'));
					return util::success('Ok');
				}
			} else {
				Err::fatal('User not exists', __FILE__, __FILE__);
			}
		}
	} catch (\Exception $e) {
		return util::failure($e->getMessage());
	}