<?php

	namespace ajax\user;


	use Exception;
	use model\page\Ajax;

	class ForgotPassword extends Ajax
	{
		private string $email;

		public function initialize()
		{
			$this->email = strip_tags($_REQUEST['email']);
			return TRUE;
		}

		public function post()
		{
			try {
				$user = $this->core->getUser(['email' => $this->email]);
				if (!$user->isNew()) {
					$authKey = $user->get('authKey');
					$t       = $user->sendMail('Восстановление доступа к ' . $_SERVER['SERVER_NAME'], <<<HTML
<p><strong>Ссылка на смену пароля</strong></p>
<p><a href="{$_SERVER['SERVER_NAME']}/user/ChangePassword?action=resume&authKey=$authKey">Востановить пароль</a></p>
HTML
					);
					if ($t === TRUE) {
						return $this->success('Вам на почту выслано письмо');
					}

					return $this->failure($t);
				}

				return $this->failure('User not exists');
			} catch (Exception $e) {
				return $this->failure($e->getMessage());
			}
		}
	}

	return ForgotPassword::class;