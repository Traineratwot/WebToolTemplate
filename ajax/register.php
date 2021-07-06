<?php

	namespace ajax;

	use core\Err;
	use core\Core;
	use core\util;

	/** @var Core $core */

	include_once '../core/engine.php';
	$email = $_REQUEST['email'];
	$password = $_REQUEST['password'];
	try {
		if ($email and $password) {
			$newUser = $core->getUser(['email' => $email]);
			if ($newUser->isNew()) {
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					Err::fatal('Please enter a valid email',__FILE__,__FILE__);
				}
				if (strlen($password) < 6) {
					Err::fatal('Please enter a password length > 5 characters',__FILE__,__FILE__);
				}
				$salt = rand(1000000, 9999999);
				$authKey = md5($password . $email . $salt);
				/** @var Core $newUser */
				$newUser->set('email', $email);
				$newUser->set('password', md5($password));
				$newUser->set('salt', $salt);
				$newUser->set('authKey', $authKey);
				$newUser->save();
				if ($newUser->isNew()) {
					Err::fatal('Failed write to DataBase',__FILE__,__FILE__);
				} else {
					util::setCookie('authKey', $authKey);
					die(util::success('Ok'));
				}
			} else {
				Err::fatal('User already exists',__FILE__,__FILE__);
			}
		} else {
			die(util::failure('empty login or password'));
		}
	} catch (\Exception $e) {
		die(util::failure($e->getMessage()));
	}
