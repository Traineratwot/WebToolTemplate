<?php

	namespace ajax\user;


	use model\main\Utilities;
	use model\page\Ajax;

	class ChangePassword extends Ajax
	{


		private string $password;

		public function initialize()
		{
			$this->password = strip_tags($_REQUEST['password']);
			return TRUE;
		}

		function post()
		{
			$this->core->auth();

			if ($this->core->isAuthenticated) {
				if ($this->password) {
					$salt    = Utilities::id(8);
					$email = $this->core->user->get('email');
					$this->core->user->setPassword($this->password);
					$authKey = md5($this->core->user->get('password') . $email . $salt);
					$this->core->user->set('authKey', $authKey);
					$this->core->user->save();
					session_unset();
					return $this->success('Ok');
				}

				return $this->failure('empty password');
			}

			return $this->failure('ошибка');
		}
	}

	return ChangePassword::class;