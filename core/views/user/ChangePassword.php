<?php

	namespace page\user;

	
	use model\main\Utilities;
	use model\page\Page;

	class ChangePassword extends Page
	{
		public $title = 'ChangePassword';

		

		public function beforeRender()
		{
			if ($_GET['action'] == 'resume' && !empty($_GET['authKey'])) {
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
					$_SESSION['ip']      = Utilities::getIp();
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

	return ChangePassword::class;