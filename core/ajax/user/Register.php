<?php

	namespace ajax\user;

	use Exception;
	use model\page\Ajax;

	class Register extends Ajax
	{
		private string $password;
		private string $email;

		public function initialize()
		{
			$this->email    = strip_tags($_REQUEST['email']);
			$this->password = strip_tags($_REQUEST['password']);
			if ($this->email and $this->password) {
				return TRUE;
			}

			return 'empty email or password';
		}

		public function process()
		{
			try {
				$newUser = $this->core->getUser(['email' => $this->email]);
				if ($newUser->isNew()) {
					$newUser->register($this->email, $this->password);
					return $this->success('Ok');
				}

				return $this->failure('User already exists');
			} catch (Exception $e) {
				return $this->failure($e->getMessage());
			}
		}
	}

	return Register::class;