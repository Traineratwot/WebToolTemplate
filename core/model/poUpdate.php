<?php

	namespace core\model;

	use Gettext\Generator\PoGenerator;
	use Gettext\Loader\PoLoader;
	use Gettext\Merge;
	use Gettext\Scanner\JsScanner;
	use Gettext\Scanner\PhpScanner;
	use Gettext\Translations;

	include_once WT_MODEL_PATH . 'smartyScan.php';

	class PoUpdate
	{
		/**
		 * @var bool|Translations
		 */
		private        $old = FALSE;
		private string $domain;
		private        $lang;

		public $strategy = Merge::REFERENCES_OURS | Merge::TRANSLATIONS_THEIRS;

		public function __construct()
		{
			if (!WT_USE_GETTEXT) {
				include_once WT_MODEL_PATH . 'phpScan2.php';
			}
		}

		function run($lang)
		{
			$this->domain = WT_LOCALE_DOMAIN;
			$this->lang   = $lang;
			chdir(WT_BASE_PATH);
			if ($lang) {
				$poLoader  = new PoLoader();
				$generator = new PoGenerator();
				$dir       = WT_LOCALE_PATH . $lang . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR;
				if (!is_dir($dir)) {
					if (!mkdir($dir, 0777, TRUE) && !is_dir($dir)) {
						throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
					}
				}
				$oldFile = $dir . WT_LOCALE_DOMAIN . '.po';
				if (file_exists($oldFile)) {
					$this->old = $poLoader->loadFile($oldFile);
				} else {
					$translations = Translations::create(WT_LOCALE_DOMAIN);
					$locale       = explode('.', $lang);
					$translations->getHeaders()
								 ->set('Language', $locale[0])
								 ->set('Content-Type:', 'text/plain; charset=UTF-8')
					;
					$generator->generateFile($translations, $oldFile);
					$this->old = $poLoader->loadFile($oldFile);
				}
				$new = $this->phpScan($this->old);
				$new = $this->jsScan($new);
				$new = $this->SmartyScan($new);
				$generator->generateFile($new, $oldFile);
			}
		}

		function poEdit()
		{
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

		function phpScan(Translations $old, $file = NULL)
		{
			if (WT_USE_GETTEXT) {
				$phpScanner = new PhpScanner(Translations::create(WT_LOCALE_DOMAIN));
				$phpScanner->setDefaultDomain(WT_LOCALE_DOMAIN);
				$phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');
				if ($file) {
					if (stripos($file, '.php') !== FALSE) {
						$phpScanner->scanFile($file);
						Console::success($file);
					}
				} else {
					foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.php', GLOB_BRACE) as $_file) {
						if (stripos($_file, 'vendor') === FALSE and stripos($_file, 'cache') === FALSE) {
							$phpScanner->scanFile($_file);
							Console::success($_file);
						}
					}
				}
				foreach ($phpScanner->getTranslations() as $translations) {
					return $translations->mergeWith($old, $this->strategy);
				}
			} else {
				$phpScanner = new PhpScanner2(Translations::create(WT_LOCALE_DOMAIN));
				$phpScanner->setDefaultDomain(WT_LOCALE_DOMAIN);
				$phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');
				if ($file) {
					if (stripos($file, '.php') !== FALSE) {
						$phpScanner->scanFile($file);
						Console::success($file);
					}
				} else {
					foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.php', GLOB_BRACE) as $_file) {
						if (stripos($_file, 'vendor') === FALSE and stripos($_file, 'cache') === FALSE) {
							$phpScanner->scanFile($_file);
							Console::success($_file);
						}
					}
				}
				foreach ($phpScanner->getTranslations() as $translations) {
					return $translations->mergeWith($old, $this->strategy);
				}
			}
		}

		function jsScan(Translations $old, $file = NULL)
		{
			if (WT_USE_GETTEXT) {
				$phpScanner = new JsScanner(Translations::create(WT_LOCALE_DOMAIN));
				$phpScanner->setDefaultDomain(WT_LOCALE_DOMAIN);
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
					return $translations->mergeWith($old, $this->strategy);
				}
			} else {
				return $old;
			}
		}

		function SmartyScan(Translations $old, $file = NULL)
		{
			$phpScanner = new SmartyScanner(Translations::create(WT_LOCALE_DOMAIN));
			$phpScanner->setDefaultDomain(WT_LOCALE_DOMAIN);
			$phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');
			if ($file) {
				if (stripos($file, '.tpl') !== FALSE) {
					$phpScanner->scanFile($file);
					Console::success($file);
				}
			} else {
				foreach (glob('{,*/,*/*/,*/*/*/,*/*/*/*/}*.tpl', GLOB_BRACE) as $_file) {
					if (stripos($_file, 'vendor') === FALSE and stripos($_file, 'cache') === FALSE) {
						$phpScanner->scanFile($_file);
						Console::success($_file);
					}
				}
			}
			foreach ($phpScanner->getTranslations() as $translations) {
				return $translations->mergeWith($old, $this->strategy);
			}
		}
	}