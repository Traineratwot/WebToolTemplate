<?php

	namespace page;

	use model\page\Page;
	use model\util;
	use traits\Utilities;

	class ChangePassword extends Page
	{
		public $alias = 'user/changePassword';
		public $title = 'ChangePassword';

		use Utilities;

		public function beforeRender()
		{
			if ($_GET['action'] == 'resume' and !empty($_GET['authKey'])) {
				$authKey = $_GET['authKey'];
				$User    = $this->core->getUser(['authKey' => $authKey]);
				if (!$User->isNew()) {
					$this->core->auth();
					$salt    = random_int(1000000, 9999999);
					$authKey = md5($authKey . $salt);
					$User->set('salt', $salt);
					$User->set('authKey', $authKey);
					$User->save();
					$_SESSION['authKey'] = $authKey;
					$_SESSION['ip']      = self::getIp();
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

	return 'ChangePassword';