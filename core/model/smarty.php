<?php

	namespace core\model;

	use SmartyBC;

	require_once 'engine.php';
	/** @var SmartyBC $smarty */
	/** @var Core $core */
	$smarty = new SmartyBC();
	$alias = $_GET['q'] ?? NULL;
	$smarty->setTemplateDir(WT_SMARTY_TEMPLATE_PATH);
	$smarty->setCompileDir(WT_SMARTY_COMPILE_PATH);
	$smarty->setConfigDir(WT_SMARTY_CONFIG_PATH);
	$smarty->setCacheDir(WT_SMARTY_CACHE_PATH);
	$smarty->assign('page', $alias);
	$smarty->assign('title', $alias);
	$smarty->assign('core', $core);
	$smarty->assign('user', $core->user);
	if ($core->user == NULL) {
		$isAuthenticated = FALSE;
	} else {
		$isAuthenticated = TRUE;
	}
	$smarty->assign('isAuthenticated', $isAuthenticated);