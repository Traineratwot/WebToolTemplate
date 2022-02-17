<?php

	namespace model\locale;

	use Gettext\Translation;
	use Gettext\Translations;

	/**
	 * Class to scan PHP files and get gettext translations
	 */
	class PoScanner
	{
		public    $trans = [];
		protected $functions
						 = [
				'gettext',
				'_',
				'__',
				'ngettext',
				'n__',
				'pgettext',
				'p__',
				'dgettext',
				'd__',
				'dngettext',
				'dn__',
				'dpgettext',
				'dp__',
				'npgettext',
				'np__',
				'dnpgettext',
				'dnp__',
				'noop',
				'noop__',
			];

		public function __construct(Translations ...$allTranslations)
		{
			foreach ($allTranslations as $translations) {
				$domain                      = $translations->getDomain();
				$this->translations[$domain] = $translations;
			}
		}

		public function setDefaultDomain($domain)
		{
			$this->domain = $domain ?: WT_LOCALE_DOMAIN;
		}

		public function extractCommentsStartingWith(string ...$prefixes)
		: self
		{
			$this->commentsPrefixes = $prefixes;

			return $this;
		}

		public function getTranslations()
		: array
		{
			return $this->translations;
		}

		public function scanFile($FileName)
		{
			$this->FileName = $FileName;
			$code           = file_get_contents($this->FileName);
			$lines          = explode("\n", $code);
			$fns            = implode("|", $this->functions);
			foreach ($lines as $line => $str) {
				$matches = NULL;
				$line++;
				$re = '/((' . $fns . ')\([\'`"](.*)[\'`"](,.+){0,}\))/misuU';
				preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
				if ($matches) {
					foreach ($matches as $match) {
						$this->translations[$this->domain]->add(
							$this->createTranslation($match[3], $line)
						);
					}
				}
			}
		}


		public function createTranslation($name, $line, $translate = '', $plural = '')
		{
			$translation = $this->trans[$name] ?? $this->trans[$name] = Translation::create(NULL, $name);
			$f = str_replace(WT_BASE_PATH, '', $this->FileName);
			$translation->getReferences()->add($f, $line);
			if ($translate) {
				$translation->translate($translate);
			}
			if ($plural) {
				$translation->translatePlural($plural);
			}
			return $translation;
		}
	}
