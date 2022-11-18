<?php

	namespace page\user;


	use model\main\Utilities;
	use model\page\Page;

	class ChangePassword extends Page
	{
		public $title = 'ChangePassword';


		public function beforeRender()
		{
			if ($_GET['action'] == 'resume' && !empty($_GET['auth_key'])) {
				$auth_key = $_GET['auth_key'];
				$User    = $this->core->getUser(['auth_key' => $auth_key]);
				if (!$User->isNew()) {
					$this->core->auth();
					$salt    = random_int(1000000, 9999999);
					$auth_key = md5($auth_key . $salt);
					$User->set('salt', $salt);
					$User->set('auth_key', $auth_key);
					$User->save();
					$_SESSION['auth_key'] = $auth_key;
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