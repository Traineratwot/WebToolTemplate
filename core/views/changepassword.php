<?php

	namespace core\page;

	use core\model\Page;
	use core\model\util;

	class Changepassword extends Page
	{
		public $alias = 'changepassword';
		public $title = 'changepassword';

		public function beforeRender()
		{
			if ($_GET['action'] == 'resume' and !empty($_GET['authKey'])) {
				$authKey = $_GET['authKey'];
				$User = $this->core->getUser(['authKey' => $authKey]);
				if (!$User->isNew()) {
					if ($User->get('password') !== md5($this->password)) {
					} else {
						util::setCookie('authKey', $User->get('authKey'));
					}
					$this->core->auth();
					$salt = random_int(1000000, 9999999);
					$authKey = md5($authKey . $salt);
					$User->set('salt', $salt);
					$User->set('authKey', $authKey);
					$User->save();
					util::setCookie('authKey', $authKey);
				}
			} else {
				if (!$this->core->isAuthenticated) {
					$this->errorPage(403);
				}
			}
		}
	}

	return 'Changepassword';