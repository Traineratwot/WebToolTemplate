<?php

	namespace page\user;

	use model\page\Page;

	class Profile extends Page
	{
		public function beforeRender()
		{
			$this->title = 'Профиль пользователя ' . $this->core->user->getName();
		}
	}

	return Profile::class;