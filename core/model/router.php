<?php

	namespace core\model;
	/** @var Core $core */
	/** @var SmartyBC $smarty */

	/** @var Core $core */
	$alias = $_GET['q'] ?? NULL;
	$ajax = $_GET['a'] ?? NULL;
	if ($ajax) {
		$ajax = WT_AJAX_PATH . $ajax . '.php';
		if (file_exists($ajax)) {
			$result = include $ajax;
			$class = 'core\ajax\\' . $result;
			if (!class_exists($class)) {
				$class = 'core\ajax\\' . $ajax;
			}
			if (!class_exists($class)) {
				Err::fatal("class '$class' is not define");
			}
			/** @var Ajax $result */
			$result = new $class($core);
			try {
				$response = $result->run();
			} catch (Exception $e) {
				$response = json_encode($result, 256);
			}
			die($response);
		}

	}
	if ($alias) {
		$page = WT_PAGES_PATH . $alias . '/' . $alias . '.tpl';
		if (file_exists($page)) {
			$view = WT_PAGES_PATH . $alias . '/' . $alias . '.php';
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
			header('Location: /profile');
		} else {
			header('Location: /login');
			die();
		}
	}