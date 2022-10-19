<?php

	namespace core\model\Events\plugins;

	use PHPMailer\PHPMailer\PHPMailer;
	use Traineratwot\PhpCli\CLI;

	interface BeforeMailSend
	{
		public function process(PHPMailer $mail);
	}
