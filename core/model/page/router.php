<?php

	namespace model\page;

	use Bramus\Router\Router;
	use Exception;
	use model\main\Core;

	/** @var Core $core */
	class WTRouterException extends Exception
	{

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
			} catch (WTRouterException $e) {
				if ($e->getCode() == 404) {
					if ($this->isAjax) {
						$this->core->errorPage();
					} else {
						try {
							$this->AdvancedRoute();
						} catch (Exception $e) {
							$this->core->errorPage();
						}
					}
				}
			}
			$this->core->errorPage();
		}

		private function selectLanguage($lang)
		{
			if ($lang) {
				$newLang = $this->core->cache->getCache('locale_' . $lang, 'locales');
				if ($newLang) {
					$this->core->setLocale($newLang, TRUE);
				} else {
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
					$this->core->cache->setCache('locale_' . $lang, $newLang, 600, 'locales');
					$this->core->setLocale($newLang, TRUE);
				}
			}
		}

		private function AdvancedRoute()
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

		private function launchPage($data = [])
		{
			$page = WT_VIEWS_PATH . $this->alias . '.php';
			if (file_exists($page)) {
				$result = include $page;
				$class  = 'page\\' . $result;
				if (!class_exists($class)) {
					$class = 'page\\' . $page;
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
					$result = new TmpPage($this->core, $this->alias, $data);
					$result->render();
					exit();
				} else {
					if (!$this->isAdvanced) {
						$this->launchAjax();
					} else {
						throw new WTRouterException('Page not found', 404);
					}
				}
			}
		}

		private function launchAjax($data = [])
		{
			$ajax = WT_AJAX_PATH . $this->alias . '.php';
			if (file_exists($ajax)) {
				$result = include $ajax;
				$class  = 'ajax\\' . $result;
				if (!class_exists($class)) {
					$class = 'ajax\\' . $ajax;
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
				throw new WTRouterException('Ajax: "' . $ajax . '" file not found', 404);
			}
		}

	}

	$r = new WTRouter($core);
	$r->route();