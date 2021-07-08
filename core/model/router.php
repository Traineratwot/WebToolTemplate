<?php
	/** @var Core $core */

	/** @var SmartyBC $smarty */

	use core\model\Core;

	$alias = $_GET['q'] ?? NULL;
	$ajax = $_GET['a'] ?? NULL;
	if ($ajax) {
		$ajax = WT_AJAX_PATH . $ajax . '.php';
		if (file_exists($ajax)) {
			$result = include $ajax;
			if (is_array($result)) {
				header('Content-Type: application/json');
				$result = json_encode($result, 256);
			}
			die($result);
		}

	}
	if ($alias) {
		$page = WT_PAGES_PATH . $alias . '.tpl';
		if (file_exists($page)) {
			$view = WT_VIEW_PATH . $alias . '.php';
			if (file_exists($view)) {
				include_once $view;
			}
			$smarty->display($page);
			die();
		} else {
			header('HTTP/1.1 404 Not Found');
			readfile(WT_PAGES_PATH . '404.html');
		}
	} else {
		if ($core->user) {
			$smarty->display(WT_CORE_PATH . 'pages/profile.tpl');
		} else {
			$smarty->display(WT_CORE_PATH . 'pages/login.tpl');
			die();
		}
	}