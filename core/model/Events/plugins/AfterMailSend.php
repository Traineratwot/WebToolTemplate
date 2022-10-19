<?php

	namespace core\model\Events\plugins;

	use PHPMailer\PHPMailer\PHPMailer;
	use Traineratwot\PhpCli\CLI;

	interface AfterMailSend
	{
		public function process(PHPMailer $mail);
	}
