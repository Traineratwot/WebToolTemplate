<?php
	require_once 'core/engine.php';
	/** @var Core $core */
	$alias = $_GET['q'] ?? NULL;
	if ($alias) {
		$view = PAGES_PATH . $alias . '.php';
		if (file_exists($view)) {
			require_once $view;
			die();
		} else {
			header('HTTP/1.1 404 Not Found');
			readfile(PAGES_PATH . '404.html');
		}
	} else {
		if ($core->user) {

		} else {
			require_once PAGES_PATH . 'login.php';
			die();
		}
	}

?>