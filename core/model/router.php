<?php

	namespace core\model;

	use Bramus\Router\Router;

	/** @var Core $core */
	$alias = mb_strtolower($_GET['q']) ?? NULL;
	$ajax  = mb_strtolower($_GET['a']) ?? NULL;
	if ($ajax) {
		goto ajax;
	}
	if (!$alias) {
		$alias = 'index';
	}
	$page = WT_VIEWS_PATH . $alias . '.php';
	if (file_exists($page)) {
		$result = include $page;
		$class  = 'core\page\\' . $result;
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
		exit();
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
		} else {
			$ajax = $alias;
			goto ajax;
		}
		exit();
	}
	exit();

	ajax:
	$ajax = WT_AJAX_PATH . $ajax . '.php';
	if (file_exists($ajax)) {
		$result = include $ajax;
		$class  = 'core\ajax\\' . $result;
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
		exit($response);
	}
	//Advanced router
	$router = include WT_CORE_PATH . "router.php";
	if (!empty($router)) {
		$switcher = new Router();
		foreach ($router as $pattern => $alias) {
			$switcher->all($pattern, function () use ($alias, $core) {
				$data = func_get_args();
				$page = WT_VIEWS_PATH . $alias . '.php';
				if (file_exists($page)) {
					$result = include $page;
					$class  = 'core\page\\' . $result;
					if (!class_exists($class)) {
						Err::fatal("class '$class' is not define", __LINE__, __FILE__);
					}
					/** @var Page $result */
					$result = new $class($core, $data);
					if ($result instanceof Page) {
						$result->render();
					} else {
						Err::fatal("Page class '$class' must be extended 'Page'", __LINE__, __FILE__);
					}
					exit();
				} else {
					$page = WT_PAGES_PATH . $alias . '.tpl';
					if (file_exists($page)) {
						class tmpPage extends Page
						{
							public function __construct(Core $core, $alias, $data)
							{
								$this->alias = $alias;
								parent::__construct($core, $data);
							}
						}

						$result = new tmpPage($core, $alias);
						$result->render();
					}
					exit();
				}
			});
		}
		$switcher->run();
		header('HTTP/1.1 404 Not Found');
		readfile(WT_PAGES_PATH . '404.html');
	}