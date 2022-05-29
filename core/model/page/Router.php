<?php

	namespace model\page;

	use Exception;
	use model\main\Core;
	use model\main\Err;
	use model\main\Utilities;
	use Traineratwot\Cache\Cache;
	use Traineratwot\Cache\CacheException;
	use Traineratwot\config\Config;
	use traits\validators\ExceptionValidate;


	class Router
	{

		public bool   $isAdvanced = FALSE;
		public bool   $isAjax;
		public string $alias;
		public ?Core  $core;
		/**
		 * @var mixed|null
		 */
		public $ln;

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
				$this->ln = Cache::getCache('routers', 'router');
				if ($this->ln[$this->alias] === 'route8') {
					goto route8;
				}
				$this->_route();
				$this->ln[$this->alias] = 'route8';
				Cache::setCache('routers', $this->ln, 600, 'router');
				route8:
				$this->advancedRoute();
				$this->core->errorPage();
			} catch (RouterException $e) {
				Err::error($e->getMessage());
				$this->core->errorPage();
			} catch (Exception $e) {
				Err::error($e->getMessage());
				$this->core->errorPage(500, 'route ErrorPage');
			}
		}

		/**
		 * @throws CacheException|ExceptionValidate
		 */
		private function selectLanguage($lang)
		{
			if ($lang) {
				$newLang = Cache::getCache('locale_' . $lang, 'locales');
				if (!$newLang) {
					$locales = scandir(Config::get('LOCALE_PATH'));
					$index   = [
						-1 => $lang,
					];
					foreach ($locales as $locale) {
						if (stripos($locale, $lang) !== FALSE || stripos($lang, $locale) !== FALSE) {
							$sim         = similar_text($lang, $locale);
							$index[$sim] = $locale;
						}
					}
					krsort($index, SORT_NUMERIC);
					$newLang = end($index);
					Cache::setCache('locale_' . $lang, $newLang, 600, 'locales');
				}
				$this->core->setLocale($newLang);
			}
		}

		//устанавливает языковой модуль

		/**
		 * @throws RouterException
		 * @throws Exception
		 */
		private function _route($data = [])
		{
			if ($this->isAjax) {
				$ajax = Utilities::findPath(Config::get('AJAX_PATH') . $this->alias . '.php');
				$this->launchAjax($ajax, $data);
			} else {
				if ($this->ln[$this->alias]) {
					switch ($this->ln[$this->alias]) {
						case 'route1':
							goto route1;
						case 'route2':
							goto route2;
						case 'route3':
							goto route3;
						case 'route4':
							goto route4;
						case 'route5':
							goto route5;
						case 'route6':
							goto route6;
						case 'route7':
							goto route7;
					}
				}
				route1:
				$page = Utilities::findPath(Config::get('VIEWS_PATH') . $this->alias . '.php');
				if ($page) {
					if (!$this->ln[$this->alias]) {
						$this->ln[$this->alias] = 'route1';
						Cache::setCache('routers', $this->ln, 600, 'router');
					}
					$this->launchPage($page, $data);
				}
				route2:
				$page = Utilities::findPath(Config::get('PAGES_PATH') . $this->alias . '.tpl');
				if ($page) {
					if (!$this->ln[$this->alias]) {
						$this->ln[$this->alias] = 'route2';
						Cache::setCache('routers', $this->ln, 600, 'router');
					}
					$this->launchPageTpl($page, $data);
				}
				route3:
				$ajax = Utilities::findPath(Config::get('AJAX_PATH') . $this->alias . '.php');
				if ($ajax) {
					if (!$this->ln[$this->alias]) {
						$this->ln[$this->alias] = 'route3';
						Cache::setCache('routers', $this->ln, 600, 'router');
					}
					$this->launchAjax($ajax, $data);
				}
				route4:
				$page = Utilities::findPath(Config::get('PAGES_PATH') . $this->alias . '.html');
				if ($page) {
					if (!$this->ln[$this->alias]) {
						$this->ln[$this->alias] = 'route4';
						Cache::setCache('routers', $this->ln, 600, 'router');
					}
					$this->launchPageHtml($page, $data);
				}
				route5:
				$page = Utilities::findPath(Config::get('VIEWS_PATH') . $this->alias . DIRECTORY_SEPARATOR . 'index.php');
				if ($page) {
					if (!$this->ln[$this->alias]) {
						$this->ln[$this->alias] = 'route5';
						Cache::setCache('routers', $this->ln, 600, 'router');
					}
					$this->launchPage($page, $data);
				}
				route6:
				$page = Utilities::findPath(Config::get('PAGES_PATH') . $this->alias . DIRECTORY_SEPARATOR . 'index.tpl');
				if ($page) {
					if (!$this->ln[$this->alias]) {
						$this->ln[$this->alias] = 'route6';
						Cache::setCache('routers', $this->ln, 600, 'router');
					}
					$this->launchPageTpl($page, $data);
				}
				route7:
				$page = Utilities::findPath(Config::get('PAGES_PATH') . $this->alias . DIRECTORY_SEPARATOR . 'index.html');
				if ($page) {
					if (!$this->ln[$this->alias]) {
						$this->ln[$this->alias] = 'route7';
						Cache::setCache('routers', $this->ln, 600, 'router');
					}
					$this->launchPageHtml($page, $data);
				}
			}
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
			$result   = new $class($this->core, $data);
			$response = '';
			try {
				if ($result instanceof Ajax) {
					try {
						$response = $result->run();
					} catch (Exception $e) {
						Err::error($e->getMessage());
					}
				} else {
					Err::fatal("Ajax class '$class' must be extended 'Ajax'");
				}
			} catch (Exception $e) {
				Err::fatal($e->getMessage());
				$response = json_encode($result, 256);
			}
			exit($response);
		}

		/**
		 * Запускает страницу из файла .php
		 * @throws RouterException
		 * @throws Exception
		 */
		private function launchPage($page, $data = [])
		{
			$class = include $page;
			if (!class_exists($class)) {
				$class = 'page\\' . $class;
				if (!class_exists($class)) {
					return;
				}
			}
			$result = new $class($this->core, $data);
			if ($this->isAdvanced) {
				$result->setAlias($this->alias);
			}
			if ($result instanceof Page) {
				try {
					$result->render();
				} catch (Exception $e) {
					Err::fatal($e->getMessage());
					$this->core->errorPage(500, 'route ErrorPage');
				}
				exit();
			}
			Err::fatal("Page class '$class' must be extended 'Page'");
		}

		/**
		 * Запускает страницу из файла .tpl
		 * @throws Exception
		 */
		private function launchPageTpl($page, $data = [])
		{
			$result = new TmpPage($this->core, $this->alias, $data, $page);
			try {
				$result->render();
			} catch (Exception $e) {
				Err::error($e->getMessage());
			}
			exit();
		}

		/**
		 * Запускает страницу из файла .html
		 * @throws Exception
		 */
		private function launchPageHtml($page, $data = [])
		{
			$result = new TmpPage($this->core, $this->alias, $data, $page);
			try {
				$result->render();
			} catch (Exception $e) {
				Err::error($e->getMessage());
			}
			exit();
		}

		/**
		 * Запускает страницу или REST на основе шаблона в ./core/router.php
		 * @throws Exception
		 */
		private function advancedRoute()
		{
			$this->isAdvanced = TRUE;
			$router_path      = Utilities::findPath(Config::get('CORE_PATH') . "router.php");
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
	}

	$r = new Router();
	try {
		$r->route();
	} catch (Exception $e) {
		Err::Fatal($e->getMessage());
	}