<?php

	namespace model\page;

	use Exception;
	use model\main\Cache;
	use model\main\Core;
	use model\main\Err;
	use traits\Utilities;

	class Router
	{
		use Utilities;

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


		public function route()
		{
			if (function_exists('WT_LOCALE_SELECT_FUNCTION')) {
				$lang = WT_LOCALE_SELECT_FUNCTION();
				$this->selectLanguage($lang);
			}
			try {
				if ($this->isAjax) {
					$this->launchAjax();
				} else {
					$this->launchPage();
				}
			} catch (RouterException $e) {
				if ($e->getCode() === 404) {
					if ($this->isAjax) {
						$this->core->errorPage();
					} else {
						try {
							$this->advancedRoute();
						} catch (Exception $e) {
							$this->core->errorPage();
						}
					}
				}
			} catch (Exception $e) {
			}
			$this->core->errorPage();
		}

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
						if (stripos($locale, $lang) !== false or stripos($lang, $locale) !== false ) {
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
		 * @throws Exception
		 */
		private function advancedRoute()
		{
			$this->isAdvanced = TRUE;
			$router_path      = self::findPath(WT_CORE_PATH . "router.php");
			$router           = include $router_path;
			if (!empty($router)) {
				$switcher = new \Bramus\Router\Router();
				$self     = $this;

				foreach ($router['ajax'] as $pattern => $alias) {
					$switcher->all($pattern, function () use ($alias, $self) {
						$data        = func_get_args();
						$self->alias = $alias;
						$self->launchAjax($data);
						exit();
					});
				}
				foreach ($router['page'] as $pattern => $alias) {
					$switcher->all($pattern, function () use ($alias, $self) {
						$data        = func_get_args();
						$self->alias = $alias;
						$self->launchPage($data);
						exit();
					});
				}
				$switcher->run();
			}
		}

		/**
		 * @throws RouterException
		 * @throws Exception
		 */
		private function launchPage($data = [])
		{
			$page = WT_VIEWS_PATH . $this->alias . '.php';
			$page = self::findPath($page);
			if ($page) {
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
					$result->render();
					exit();
				}
				Err::fatal("Page class '$class' must be extended 'Page'", __LINE__, __FILE__);
			} else {
				$page = WT_PAGES_PATH . $this->alias . '.tpl';
				$page = self::findPath($page);
				if ($page) {
					$result = new TmpPage($this->core, $this->alias, $data);
					$result->render();
					exit();
				}
				if (!$this->isAdvanced) {
					$this->launchAjax();
				} else {
					throw new RouterException('Page not found', 404);
				}
			}
		}

		/**
		 * @throws RouterException
		 * @throws Exception
		 */
		private function launchAjax($data = [])
		{
			$ajax = WT_AJAX_PATH . $this->alias . '.php';
			$ajax = self::findPath($ajax);
			if ($ajax) {
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
			throw new RouterException('Ajax: "' . $ajax . '" file not found', 404);
		}
	}

	$r = new Router();
	$r->route();