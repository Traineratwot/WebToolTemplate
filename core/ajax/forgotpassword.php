<?php

	namespace ajax;


	use model\page\Ajax;

	class ForgotPassword extends Ajax
	{
		public function initialize()
		{
			$this->email = strip_tags($_REQUEST['email']);
			return TRUE;
		}

		public function post()
		{
			try {
				/** @var user $User */
				$User = $this->core->getUser(['email' => $this->email]);
				if (!$User->isNew()) {
					$authKey = $User->get('authKey');
					$t       = $User->sendMail('Восстановление доступа к ' . $_SERVER['SERVER_NAME'], <<<HTML
<p><strong>Ссылка на смену пароля</strong></p>
<p><a href="{$_SERVER['SERVER_NAME']}/changepassword?action=resume&authKey={$authKey}">{$_SERVER['SERVER_NAME']}/changepassword?action=resume&authKey={$authKey}</a></p>
HTML
					);
					if ($t === TRUE) {
						return $this->success('Вам на почту выслано письмо');
					} else {
						return $this->failure($t);
					}
				} else {
					return $this->failure('User not exists');
				}
			} catch (\Exception $e) {
				return $this->failure($e->getMessage());
			}
		}
	}

	return 'ForgotPassword';