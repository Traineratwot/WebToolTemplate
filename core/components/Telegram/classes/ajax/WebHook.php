<?php

	namespace core\components\Telegram\classes\ajax;

	use model\page\Ajax;

	class WebHook extends Ajax
	{
		public function process()
		{
			return $this->success('I am AJAX', $this->data);
		}
	}

	return WebHook::class;