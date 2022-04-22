<?php

	namespace page\user;

	use model\page\Page;

	class Profile extends Page
	{
		public function beforeRender()
		{
			if ($this->core->isAuthenticated) {
				$this->title = 'Профиль пользователя ' . $this->core->user->getName();
			}
		}
	}

	return Profile::class;