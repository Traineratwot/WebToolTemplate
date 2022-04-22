<?php

	namespace model\page;

	use Exception;
	use model\main\Cache;
	use model\main\Core;
	use model\main\Err;
	use model\main\Utilities;


	class Router
	{


		private $isAjax;
		private $alias;
		private $core;
		private $isAdvanced = FALSE;

		public function __construct()
		{
			$this->core = Core::init();
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


		/**
		 * @throws Exception
		 */
		public function route()
		{
			if (function_exists('WT_LOCALE_SELECT_FUNCTION')) {
				$lang = WT_LOCALE_SELECT_FUNCTION();
				$this->selectLanguage($lang);
			}
			try {
				$this->_route();
				$this->advancedRoute();
			} catch (RouterException $e) {
				$this->core->errorPage();
			} catch (Exception $e) {
				Err::error($e->getMessage(), __LINE__, __FILE__);
				$this->core->errorPage(500, 'route ErrorPage');
			}
		}

		/**
		 * @throws RouterException
		 * @throws Exception
		 */
		private function _route($data = [])
		{
			if ($this->isAjax) {
				$ajax = Utilities::findPath(WT_AJAX_PATH . $this->alias . '.php');
				$this->launchAjax($ajax, $data);
			} else {
				$page = Utilities::findPath(WT_VIEWS_PATH . $this->alias . '.php');
				if ($page) {
					$this->launchPage($page, $data);
				}
				$page = Utilities::findPath(WT_PAGES_PATH . $this->alias . '.tpl');
				if ($page) {
					$this->launchPageTpl($page, $data);
				}
				$ajax = Utilities::findPath(WT_AJAX_PATH . $this->alias . '.php');
				if ($ajax) {
					$this->launchAjax($ajax, $data);
				}
				$page = Utilities::findPath(WT_VIEWS_PATH . $this->alias . DIRECTORY_SEPARATOR . 'index.php');
				if ($page) {
					$this->launchPage($page, $data);
				}
				$page = Utilities::findPath(WT_PAGES_PATH . $this->alias . DIRECTORY_SEPARATOR . 'index.tpl');
				if ($page) {
					$this->launchPageTpl($page, $data);
				}
			}
		}

		//устнавливает языковой модуль
		private function selectLanguage($lang)
		{
			if ($lang) {
				$newLang = Cache::getCache('locale_' . $lang, 'locales');
				if (!$newLang) {
					$locales = scandir(WT_LOCALE_PATH);
					$index   = [
						-1 => $lang,
					];
					foreach ($locales as $locale) {
						if (stripos($locale, $lang) !== FALSE or stripos($lang, $locale) !== FALSE) {
							$sim         = similar_text($lang, $locale);
							$index[$sim] = $locale;
						}
					}
					ksort($index, SORT_NUMERIC);
					$newLang = end($index);
					Cache::setCache('locale_' . $lang, $newLang, 600, 'locales');
				}
				$this->core->setLocale($newLang, TRUE);
			}
		}

		/**
		 * Запускает страницу или REST на основе шаблона в ./core/router.php
		 * @throws Exception
		 */
		private function advancedRoute()
		{
			$this->isAdvanced = TRUE;
			$router_path      = Utilities::findPath(WT_CORE_PATH . "router.php");
			$router           = include $router_path;
			if (!empty($router)) {
				$switcher = new \Bramus\Router\Router();
				$self     = $this;

				foreach ($router['ajax'] as $pattern => $alias) {
					$switcher->all($pattern, function () use ($alias, $self) {
						$data         = func_get_args();
						$self->isAjax = TRUE;
						$self->alias  = $alias;
						$self->_route($data);
						exit();
					});
				}
				foreach ($router['page'] as $pattern => $alias) {
					$switcher->all($pattern, function () use ($alias, $self) {
						$data        = func_get_args();
						$self->alias = $alias;
						$self->_route($data);
						exit();
					});
				}
				$switcher->run();
			}
		}

		/**
		 * запускает старницу из файла .php
		 * @throws RouterException
		 * @throws Exception
		 */
		private function launchPage($page, $data = [])
		{
			$class = include $page;
			if (!class_exists($class)) {
				$class = 'page\\' . $class;
				if (!class_exists($class)) {
					$this->launchAjax();
					return;
				}
			}
			/** @var Page $result */
			$result = new $class($this->core, $data);
			if ($result instanceof Page) {
				try {
					$result->render();
				} catch (Exception $e) {
					Err::error($e->getMessage, __LINE__, __FILE__);
				}
				exit();
			}
			Err::fatal("Page class '$class' must be extended 'Page'", __LINE__, __FILE__);
		}

		/**
		 * запускает старницу из файла .tpl
		 * @throws Exception
		 */
		private function launchPageTpl($page, $data = [])
		{
			$result = new TmpPage($this->core, $this->alias, $data, $page);
			try {
				$result->render();
			} catch (Exception $e) {
				Err::error($e->getMessage, __LINE__, __FILE__);
			}
			exit();
		}

		/**
		 * Запускает REST controller
		 * @throws RouterException
		 * @throws Exception
		 */
		private function launchAjax($ajax, $data = [])
		{
			$class = include $ajax;
			if (!class_exists($class)) {
				$class = 'ajax\\' . $class;
				if (!class_exists($class)) {
					Err::fatal("class '$class' is not define");
				}
			}
			/** @var Ajax $result */
			$result = new $class($this->core, $data);
			try {
				if ($result instanceof Ajax) {
					try {
						$response = $result->run();
					} catch (Exception $e) {
						Err::error($e->getMessage, __LINE__, __FILE__);
					}
				} else {
					Err::fatal("Ajax class '$class' must be extended 'Ajax'", __LINE__, __FILE__);
				}
			} catch (Exception $e) {
				Err::fatal($e->getMessage(), __LINE__, __FILE__);
				$response = json_encode($result, 256);
			}
			exit($response);
		}
	}

	$r = new Router();
	try {
		$r->route();
	} catch (Exception $e) {
		Err::Fatal($e->getMessage(), __LINE__, __FILE__);
	}