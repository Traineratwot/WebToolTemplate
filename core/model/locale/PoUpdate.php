<?php

	namespace model\locale;

	use Gettext\Generator\PoGenerator;
	use Gettext\Loader\PoLoader;
	use Gettext\Merge;
	use Gettext\Scanner\JsScanner;
	use Gettext\Scanner\PhpScanner;
	use Gettext\Translations;
	use RuntimeException;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Console;


	include_once Config::get('MODEL_PATH') . 'locale/SmartyScanner.php';

	class PoUpdate
	{
		public $strategy = 0;
		/**
		 * @var bool|Translations
		 */
		private $old = FALSE;
		private $domain;
		private $lang;

		public function __construct()
		{
			if (!Config::get('USE_GETTEXT')) {
				include_once Config::get('MODEL_PATH') . 'locale/poScan.php';
			}
		}

		public function run($lang)
		{
			$this->domain = Config::get('LOCALE_DOMAIN');
			$this->lang   = $lang;
			chdir(Config::get('BASE_PATH'));
			if ($lang) {
				$poLoader  = new PoLoader();
				$generator = new PoGenerator();
				$dir       = Config::get('LOCALE_PATH') . $lang . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR;
				if (!is_dir($dir) && !mkdir($dir, 0777, TRUE) && !is_dir($dir)) {
					throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
				}
				$oldFile = $dir . Config::get('LOCALE_DOMAIN') . '.po';
				if (!file_exists($oldFile)) {
					$translations = Translations::create(Config::get('LOCALE_DOMAIN'));
					$locale       = explode('.', $lang);
					$translations->getHeaders()
								 ->set('Language', $locale[0])
								 ->set('Content-Type:', 'text/plain; charset=UTF-8')
					;
					$generator->generateFile($translations, $oldFile);
				}
				$this->old = $poLoader->loadFile($oldFile);
				$new       = $this->phpScan(Translations::create(Config::get('LOCALE_DOMAIN')));
				$new       = $this->jsScan($new);
				$new       = $this->SmartyScan($new);
				$new       = $this->old->mergeWith($new, Merge::TRANSLATIONS_THEIRS);
				$generator->generateFile($new, $oldFile,);
			}
		}

		function phpScan(Translations $old, $file = NULL)
		{
			if (Config::get('USE_GETTEXT')) {
				$phpScanner = new PhpScanner(Translations::create(Config::get('LOCALE_DOMAIN')));
				$phpScanner->setDefaultDomain(Config::get('LOCALE_DOMAIN'));
				$phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');
				if ($file) {
					if (stripos($file, '.php') !== FALSE) {
						$phpScanner->scanFile($file);
						Console::success($file);
					}
				} else {
					foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.php', GLOB_BRACE) as $_file) {
						if (stripos($_file, 'vendor') === FALSE && stripos($_file, 'cache') === FALSE) {
							$phpScanner->scanFile($_file);
							Console::success($_file);
						}
					}
				}
				foreach ($phpScanner->getTranslations() as $translations) {
					$old = $old->mergeWith($translations, $this->strategy);
				}
				return $old;
			} else {
				$phpScanner = new poScan(Translations::create(Config::get('LOCALE_DOMAIN')));
				$phpScanner->setDefaultDomain(Config::get('LOCALE_DOMAIN'));
				$phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');
				if ($file) {
					if (stripos($file, '.php') !== FALSE) {
						$phpScanner->scanFile($file);
						Console::success($file);
					}
				} else {
					foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.php', GLOB_BRACE) as $_file) {
						if (stripos($_file, 'vendor') === FALSE && stripos($_file, 'cache') === FALSE) {
							$phpScanner->scanFile($_file);
							Console::success($_file);
						}
					}
				}
				foreach ($phpScanner->getTranslations() as $translations) {
					$old = $old->mergeWith($translations, $this->strategy);
				}
				return $old;
			}
		}

		function jsScan(Translations $old, $file = NULL)
		{
			if (Config::get('USE_GETTEXT')) {
				$phpScanner = new JsScanner(Translations::create(Config::get('LOCALE_DOMAIN')));
				$phpScanner->setDefaultDomain(Config::get('LOCALE_DOMAIN'));
				$phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');
				if ($file) {
					if (stripos($file, '.js') !== FALSE) {
						$phpScanner->scanFile($file);
						Console::success($file);
					}
				} else {
					foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.js', GLOB_BRACE) as $_file) {
						if (stripos($_file, 'node_modules') === FALSE) {
							$phpScanner->scanFile($_file);
							Console::success($_file);
						}
					}
				}
				foreach ($phpScanner->getTranslations() as $translations) {
					$old = $old->mergeWith($translations, $this->strategy);
				}
				return $old;
			} else {
				$phpScanner = new poScan(Translations::create(Config::get('LOCALE_DOMAIN')));
				$phpScanner->setDefaultDomain(Config::get('LOCALE_DOMAIN'));
				$phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');
				if ($file) {
					if (stripos($file, '.js') !== FALSE) {
						$phpScanner->scanFile($file);
						Console::success($file);
					}
				} else {
					foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.js', GLOB_BRACE) as $_file) {
						if (stripos($_file, 'node_modules') === FALSE && stripos($_file, 'highlight') === FALSE) {
							$phpScanner->scanFile($_file);
							Console::success($_file);
						}
					}
				}
				foreach ($phpScanner->getTranslations() as $translations) {
					$old = $old->mergeWith($translations, $this->strategy);
				}
				return $old;
			}
		}

		function SmartyScan(Translations $old, $file = NULL)
		{
			$phpScanner = new SmartyScanner(Translations::create(Config::get('LOCALE_DOMAIN')));
			$phpScanner->setDefaultDomain(Config::get('LOCALE_DOMAIN'));
			$phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');
			if ($file) {
				if (stripos($file, '.tpl') !== FALSE) {
					$phpScanner->scanFile($file);
					Console::success($file);
				}
			} else {
				foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.tpl', GLOB_BRACE) as $_file) {
					if (stripos($_file, 'vendor') === FALSE && stripos($_file, 'cache') === FALSE) {
						$phpScanner->scanFile($_file);
						Console::success($_file);
					}
				}
			}
			foreach ($phpScanner->getTranslations() as $translations) {
				$old = $old->mergeWith($translations, $this->strategy);
			}
			return $old;
		}

		function poEdit()
		{
			//php C:\OpenServer\domains\WebToolTemplate\poEdit.extractor.php %o %f
			global $argv;
			$o         = $argv[1];
			$f         = $argv[2];
			$poLoader  = new PoLoader();
			$this->old = $poLoader->loadFile($o);
			$generator = new PoGenerator();
			$new       = $this->phpScan($this->old, $f);
			$new       = $this->jsScan($new, $f);
			$new       = $this->SmartyScan($new, $f);
			$generator->generateFile($new, $o);
		}
	}