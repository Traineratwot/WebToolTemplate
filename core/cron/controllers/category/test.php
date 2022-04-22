<?php


	use model\cli\Console;
	use model\main\Core;

	Console::info('sleep 5');
	$core = Core::init();
	$core->db->query('SELECT * FROM users');
	$core->db->query('SELECT * FROM users');
	$core->db->query('SELECT * FROM users');