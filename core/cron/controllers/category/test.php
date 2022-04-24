<?php


	
	use model\main\Core;
	use Traineratwot\PhpCli\Console;

	Console::info('sleep 5');
	$core = Core::init();
	$core->db->query('SELECT * FROM users');
	$core->db->query('SELECT * FROM users');
	$core->db->query('SELECT * FROM users');