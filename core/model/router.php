<?php

	namespace core\model;

	use Bramus\Router\Router;
	use Exception;

	/** @var Core $core */
	class tmpPage extends Page
	{
		public function __construct(Core $core, $alias, $data = [])
		{
			$this->alias = $alias;
			parent::__construct($core, $data);
		}
	}

	class WTRouter
	{
		private bool   $isAjax;
		private string $alias;
		private Core   $core;
		private Router $switcher;
		private bool   $isAdvanced = FALSE;

		public function __construct(Core $core)
		{
			$this->core = $core;
			if (isset($_GET['a'])) {
				$this->isAjax = TRUE;
				$this->alias  = mb_strtolower($_GET['a']);
			} elseif (isset($_GET['q'])) {
				$this->isAjax = FALSE;
				$this->alias  = mb_strtolower($_GET['q']);
			} else {
				$this->isAjax = FALSE;
				$this->alias  = 'index';
			}

		}

		public function route()
		{
			try {
				if ($this->isAjax) {
					$this->launchAjax();
				} else {
					$this->launchPage();
				}
			} catch (Exception $e) {
				if ($e->getCode() == 404) {
					if ($this->isAjax) {
						$this->ErrorPage();
					} else {
						try {
							$this->AdvancedRoute();
						} catch (Exception $e) {
							$this->ErrorPage();
						}
					}
				}
			}
			$this->ErrorPage();
		}

		public function AdvancedRoute()
		{
			$this->isAdvanced = TRUE;
			$router           = include WT_CORE_PATH . "router.php";
			if (!empty($router)) {
				$this->switcher = new Router();
				$self           = $this;

				foreach ($router['ajax'] as $pattern => $alias) {
					$this->switcher->all($pattern, function () use ($alias, $self) {
						$data        = func_get_args();
						$self->alias = $alias;
						$self->launchAjax($data);
						exit();
					});
				}
				foreach ($router['page'] as $pattern => $alias) {
					$this->switcher->all($pattern, function () use ($alias, $self) {
						$data        = func_get_args();
						$self->alias = $alias;
						$self->launchPage($data);
						exit();
					});
				}
				$this->switcher->run();
			}
		}

		public function launchPage($data = [])
		{
			$page = WT_VIEWS_PATH . $this->alias . '.php';
			if (file_exists($page)) {
				$result = include $page;
				$class  = 'core\page\\' . $result;
				if (!class_exists($class)) {
					$class = 'core\page\\' . $page;
				}
				if (!class_exists($class)) {
					Err::fatal("class '$class' is not define", __LINE__, __FILE__);
				}
				/** @var Page $result */
				$result = new $class($this->core, $data);
				if ($result instanceof Page) {
					$result->render();
					exit();
				} else {
					Err::fatal("Page class '$class' must be extended 'Page'", __LINE__, __FILE__);
				}
			} else {
				$page = WT_PAGES_PATH . $this->alias . '.tpl';
				if (file_exists($page)) {
					$result = new tmpPage($this->core, $this->alias, $data);
					$result->render();
					exit();
				} else {
					if (!$this->isAdvanced) {
						$this->launchAjax();
					} else {
						throw new Exception('Page not found', 404);
					}
				}
			}
		}

		public function ErrorPage($code = 404, $msg = 'Not Found')
		{
			header("HTTP/1.1 {$code} {$msg}");
			readfile(WT_PAGES_PATH . 'errors/' . $code . '.html');
			die;
		}

		public function launchAjax($data = [])
		{
			$ajax = WT_AJAX_PATH . $this->alias . '.php';
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
				$result = new $class($this->core, $data);
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
			} else {
				throw new Exception('Ajax: "' . $ajax . '" file not found', 404);
			}
		}

	}

	$r = new WTRouter($core);
	$r->route();