<?php
	require_once 'core/engine.php';
	/** @var Core $core */
	$alias = $_GET['q'] ?? NULL;
	$smarty = new SmartyBC();

	$smarty->setTemplateDir(SMARTY_TEMPLATE);
	$smarty->setCompileDir(SMARTY_COMPILE);
	$smarty->setConfigDir(SMARTY_CONFIG);
	$smarty->setCacheDir(SMARTY_CACHE);
	$smarty->assign('page', $alias);
	$smarty->assign('title', $alias);
	$smarty->assign('CORE_PATH', CORE_PATH);
	$smarty->assign('SMARTY_TEMPLATE', SMARTY_TEMPLATE);
	$smarty->assign('core', $core);
	$smarty->assign('user', $core->user);
	if ($core->user == NULL) {
		$isAuthenticated = FALSE;
	} else {
		$isAuthenticated = TRUE;
	}
	$smarty->assign('isAuthenticated', $isAuthenticated);
	if ($alias) {
		$view = PAGES_PATH . $alias . '.tpl';
		if (file_exists($view)) {

			$smarty->display($view);
			die();
		} else {
			header('HTTP/1.1 404 Not Found');
			readfile(PAGES_PATH . '404.html');
		}
	} else {
		if ($core->user) {
			$smarty->display(BASE_PATH . 'pages/profile.tpl');
		} else {
			$smarty->display(BASE_PATH . 'pages/login.tpl');
			die();
		}
	}

?>