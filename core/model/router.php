<?php

	namespace core\model;
	/** @var Core $core */
	$alias = mb_strtolower($_GET['q']) ?? NULL;
	$ajax = mb_strtolower($_GET['a']) ?? NULL;
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
				if ($result instanceof Ajax) {
					$response = $result->run();
				} else {
					Err::fatal("Ajax class '$class' must be extended 'Ajax'", __LINE__, __FILE__);
				}
			} catch (Exception $e) {
				Err::fatal($e->getMessage(), __LINE__, __FILE__);
				$response = json_encode($result, 256);
			}
			die($response);
		}

	}
	if ($alias) {
		$page = WT_VIEWS_PATH . $alias . '.php';
		if (file_exists($page)) {
			$result = include $page;
			$class = 'core\page\\' . $result;
			if (!class_exists($class)) {
				$class = 'core\ajax\\' . $ajax;
			}
			if (!class_exists($class)) {
				Err::fatal("class '$class' is not define", __LINE__, __FILE__);
			}
			/** @var Page $result */
			$result = new $class($core);
			if ($result instanceof Page) {
				$result->render();
			} else {
				Err::fatal("Page class '$class' must be extended 'Page'", __LINE__, __FILE__);
			}
			die();
		} else {
			$page = WT_PAGES_PATH . $alias . '.tpl';
			if (file_exists($page)) {
				class tmpPage extends Page
				{
					public function __construct(Core $core, $alias)
					{
						$this->alias = $alias;
						parent::__construct($core);
					}
				}

				$result = new tmpPage($core, $alias);
				$result->render();
				die();
			}
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