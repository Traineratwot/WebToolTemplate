<?php

	namespace model\page;

	use Exception;
	use model\Events\Event;
	use model\main\Core;
	use model\main\CoreObject;
	use model\main\Err;
	use model\main\ErrorPage;
	use model\main\Utilities;
	use Smarty;
	use SmartyException;
	use tables\Users;
	use Traineratwot\config\Config;

	/**
	 * Класс для Страницы
	 */
	abstract class Page extends CoreObject implements ErrorPage
	{


		public $alias;
		public $title;
		public $data = NULL;
		/**
		 * @var mixed|string
		 */
		public        $source;
		public Smarty $smarty;

		/**
		 * @throws Exception
		 */
		public function __construct(Core $core, $data = [])
		{
			parent::__construct($core);
			if (!empty($data)) {
				$this->data = $data;
			}
			if (!$this->alias) {
				$this->alias  = mb_strtolower(rtrim($_GET['a'], '/'));
			}
			if (!$this->source) {
				$this->prepareAlias();
			}
			if (!$this->title) {

				$this->title = Utilities::basename($this->alias) ?: $this->alias;
			}
			$this->smarty = new Smarty();
			$this->init();
		}

		/**
		 * @throws Exception
		 */
		public function prepareAlias()
		{
			if (strpos($this->alias, 'string:') === 0 || strpos($this->alias, 'eval:') === 0) {
				$this->source = $this->alias;
			} elseif (strpos($this->alias, 'chunk:') === 0 || strpos($this->alias, 'file:') === 0) {
				$this->source = Config::get('TEMPLATES_PATH') . preg_replace("@^(chunk|file):@i", '', $this->alias) . '.tpl';
				if (!file_exists($this->source)) {
					$this->source = Config::get('TEMPLATES_PATH') . preg_replace("@^(chunk|file):@i", '', $this->alias);
					if (!file_exists($this->source)) {
						throw new \RuntimeException('Chunk error: "' . $this->source . '" file not found ');
					}
				}
			} else {
				$this->source = Utilities::findPath(Config::get('PAGES_PATH') . $this->alias . '.tpl');
				if (!$this->source && is_dir(Config::get('PAGES_PATH') . $this->alias)) {
					$this->source = Utilities::findPath(Config::get('PAGES_PATH') . $this->alias . DIRECTORY_SEPARATOR . 'index.tpl');
				}
			}
		}

		/**
		 * @throws SmartyException
		 */
		public function init()
		{
			$this->smarty->addPluginsDir(Config::get('SMARTY_PLUGINS_PATH') . '/');
			$this->smarty->setTemplateDir(Config::get('SMARTY_TEMPLATE_PATH') . '/');
			$this->smarty->setCompileDir(Config::get('SMARTY_COMPILE_PATH') . '/');
			$this->smarty->setConfigDir(Config::get('SMARTY_CONFIG_PATH') . '/');
			$this->smarty->setCacheDir(Config::get('SMARTY_CACHE_PATH') . '/');
			$this->smarty->assign('page', $this);
			$this->smarty->assignGlobal('core', $this->core);
			$this->smarty->assignGlobal('user', $this->core->user);
			$this->smarty->assignGlobal('_GET', $_GET);
			$this->smarty->assignGlobal('_POST', $_POST);
			$this->smarty->assignGlobal('_COOKIE', $_COOKIE);
			$this->smarty->assignGlobal('_REQUEST', $_REQUEST);
			$this->smarty->assignGlobal('_SERVER', $_SERVER);
			$this->smarty->assignGlobal('isAuthenticated', $this->core->isAuthenticated);
			$this->addModifier('user', '\model\page\Page::modifier_user');
			$this->addModifier('chunk', '\model\page\Page::chunk');
		}

		/**
		 * @throws SmartyException
		 */
		public function addModifier($name, $function)
		{
			$this->smarty->registerPlugin("modifier", $name, $function);
		}

		/**
		 * @return int|Users
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
			if ($code) {
				http_response_code($code);
			}
			header("Location: $alias");
		}

		/**
		 * @throws Exception
		 */
		public static function chunk($alias, $values = [])
		{
			$core = Core::init();
			return (new Chunk($core, $alias, $values))->render(TRUE);
		}

		/**
		 * @throws SmartyException
		 * @throws Exception
		 */
		public function render($return = FALSE)
		{
			Event::emit('BeforeRender', NULL, $this);
			$this->beforeRender();
			$this->smarty->assignGlobal('title', $this->title);
			if (!str_starts_with($this->source, 'string:') && !str_starts_with($this->source, 'eval:')) {
				$this->source = Utilities::pathNormalize($this->source);
				if (!file_exists($this->source)) {
					Err::error('can`t load: "' . $this->source . '"');
					$this->errorPage(404);
					return FALSE;
				}
			}
			$page = $this->smarty->fetch($this->source);
			$mod  = Event::emit('AfterRender', NULL, $page, $this);
			if ($mod) {
				$page = $mod;
			}
			if ($return) {
				return $page;
			}
			echo $page;
			return TRUE;
		}

		public function beforeRender()
		{

		}

		/**
		 * @throws Exception
		 */
		public function errorPage($code = 404, $msg = 'Not Found')
		{
			$this->core->errorPage($code, $msg);
		}

		/**
		 * @throws Exception
		 */
		public function setAlias($alias)
		{
			$this->alias = $alias;
			$this->prepareAlias();
		}

		public function forward($alias)
		{
			if (file_exists(Config::get('PAGES_PATH') . $alias . '.tpl')) {
				$this->alias  = $alias;
				$this->source = Config::get('PAGES_PATH') . $alias . '.tpl';
				return TRUE;
			}

			return FALSE;
		}

		public function setVar($name, $var, $nocache = FALSE)
		{
			$this->smarty->assign($name, $var, $nocache);
		}
	}

