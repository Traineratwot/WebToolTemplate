<?php

	namespace core\model\Events\plugins;

	use Exception;
	use PHPMailer\PHPMailer\PHPMailer;

	interface onEmailSendError
	{
		public function process(PHPMailer $mail, Exception $e);
	}
