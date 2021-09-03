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
					$this->core->auth();
					$salt = random_int(1000000, 9999999);
					$authKey = md5($authKey . $salt);
					$User->set('salt', $salt);
					$User->set('authKey', $authKey);
					$User->save();
					$_SESSION['authKey'] = $authKey;
					$_SESSION['ip'] = util::getIp();
				} else {
					echo '<pre>';
					echo("<h3>Страница больше недоступна. Запросите восстановление еще раз</h3>");
					die;
				}
			} else {
				if (!$this->core->isAuthenticated) {
					$this->errorPage(403);
				}
			}
		}
	}

	return 'Changepassword';