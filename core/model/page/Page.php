<?php

	namespace model\page;

	use classes\traits\Utilities;
	use model\CoreObject;
	use model\ErrorPage;
	use SmartyBC;

	/**
	 * Класс для Страницы
	 */
	abstract class Page extends CoreObject implements ErrorPage
	{
		use Utilities;

		public $alias;
		public $title;
		public $data = NULL;

		public function __construct(Core $core, $data = [])
		{
			parent::__construct($core);
			if (!empty($data)) {
				$this->data = $data;
			}
			if (!$this->alias) {
				$this->alias = $_GET['q'];
			}
			if (strpos($this->alias, 'string:') === 0 or strpos($this->alias, 'eval:') === 0) {
				$this->source = $this->alias;
			} elseif (strpos($this->alias, 'chunk:') === 0 or strpos($this->alias, 'file:') === 0) {
				$this->source = WT_TEMPLATES_PATH . preg_replace("@^(chunk|file):@i", '', $this->alias) . '.tpl';
			} else {
				$this->source = WT_PAGES_PATH . $this->alias . '.tpl';

			}
			if (!$this->title) {

				$this->title = self::basename($this->alias) ?: $this->alias;
			}
			$this->smarty = new SmartyBC();
			$this->init();
		}

		public function init()
		{
			$this->smarty->addPluginsDir(WT_SMARTY_PLUGINS_PATH . '/');
			$this->smarty->setTemplateDir(WT_SMARTY_TEMPLATE_PATH . '/');
			$this->smarty->setCompileDir(WT_SMARTY_COMPILE_PATH . '/');
			$this->smarty->setConfigDir(WT_SMARTY_CONFIG_PATH . '/');
			$this->smarty->setCacheDir(WT_SMARTY_CACHE_PATH . '/');
			$this->smarty->assignGlobal('page', $this);
			$this->smarty->assignGlobal('core', $this->core);
			$this->smarty->assignGlobal('user', $this->core->user);
			$this->smarty->assignGlobal('_GET', $_GET);
			$this->smarty->assignGlobal('_POST', $_POST);
			$this->smarty->assignGlobal('_COOKIE', $_COOKIE);
			$this->smarty->assignGlobal('_REQUEST', $_REQUEST);
			$this->smarty->assignGlobal('_SERVER', $_SERVER);
			$this->smarty->assignGlobal('isAuthenticated', $this->core->isAuthenticated);
			$this->addModifier('user', '\model\Page::modifier_user');
			$this->addModifier('chunk', '\model\Page::chunk');
		}

		public function addModifier($name, $function)
		{
			$this->smarty->registerPlugin("modifier", $name, $function);
		}

		/**
		 * @return mixed
		 */
		public static function modifier_user($value)
		{
			global $core;
			$value = (int)$value;
			if ($value) {
				return $core->getUser($value);
			}
			return $value;
		}

		public static function redirect($alias, $code = 302)
		{
			http_response_code($code);
			header("Location: $alias");
		}

		public static function chunk($alias, $values = [])
		{
			global $core;
			$a = new Chunk($core, $alias, $values);
			return $a->render(TRUE);
		}

		public function render($return = FALSE)
		{
			if ($return) {
				ob_end_flush();
				ob_start();
			}
			$this->beforeRender();
			$this->smarty->assignGlobal('title', $this->title);
			if (strpos($this->source, 'string:') !== 0 and strpos($this->source, 'eval:') !== 0) {
				if (!file_exists($this->source)) {
					Err::fatal('can`t load: ' . $this->source);
					return FALSE;
				}
			}
			$this->smarty->display($this->source);
			if ($return) {
				return ob_get_clean();
			}
			return TRUE;
		}

		public function beforeRender()
		{

		}

		public function forward($alias)
		{
			if (file_exists(WT_PAGES_PATH . $alias . '.tpl')) {
				$this->alias  = $alias;
				$this->source = WT_PAGES_PATH . $alias . '.tpl';
				return TRUE;
			} else {
				return FALSE;
			}
		}

		public function setVar($name, $var, $nocache = FALSE)
		{
			$this->smarty->assign($name, $var, $nocache);
		}

		public function errorPage($code = 404, $msg = 'Not Found')
		{
			$this->core->errorPage($code, $msg);
		}
	}

