<?php

	namespace model\locale;

	use Gettext\Translation;
	use Gettext\Translations;

	/**
	 * Class to scan PHP files && get gettext translations
	 */
	class SmartyScanner
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
			$currentTBlock  = NULL;
			$TBlocks        = [];
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
				if (!$currentTBlock) {
					$start = stripos($str, Tblock::start);
					if ($start !== FALSE) {
						$currentTBlock            = new Tblock();
						$currentTBlock->startLine = $line;
						$currentTBlock->startChar = $start;
					}
				}
				if (!is_null($currentTBlock) && $currentTBlock instanceof Tblock) {

					$startEnd = stripos($str, Tblock::startEnd);
					if ($startEnd !== FALSE && $currentTBlock->startEndLine < 0 && $currentTBlock->startChar < $startEnd) {
						$currentTBlock->startEndLine = $line;
						$currentTBlock->startEndChar = $startEnd;
					}
					if ($currentTBlock->startEndLine <= $line && $currentTBlock->start >= 0) {
						$re = '/((\b\w+\b)=((("|\')(.+?)("|\'))|((\b)(.+?)(\b))))/';
						preg_match_all($re, $str, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE, 0);
						foreach ($matches as $match) {
							if ($match[0][1] >= $start && $match[0][1] <= $startEnd) {

								if (isset($match[8])) {
									$currentTBlock->addArg($match[2][0], $match[8][0]);
								} else {
									$currentTBlock->addArg($match[2][0], $match[6][0]);
								}
							}
						}
					}
				}
				$end = stripos($str, Tblock::end);

				if ($end !== FALSE && $currentTBlock->endLine < 0) {
					$currentTBlock->endLine = $line;
					$currentTBlock->endChar = $end;
					$TBlocks[]              = $currentTBlock;
					$currentTBlock          = NULL;
				}
			}
			foreach ($TBlocks as $bl) {
				if ($bl->startLine >= 0 && $bl->startEndLine >= 0 && $bl->startEndLine >= 0) {
					foreach ($lines as $line => $str) {
						$line++;
						if ($line === $bl->startEndLine) {
							if ($line === $bl->endLine) {
								$length   = $bl->endChar - $bl->startEndChar - 1;
								$bl->text .= substr($str, $bl->startEndChar + 1, $length);
								break;
							} else {
								$length   = count($str) - $bl->startEndChar - 1;
								$bl->text .= substr($str, $bl->startEndChar + 1, $length);
							}
						} else {
							if ($line === $bl->endLine) {
								$bl->text .= substr($str, 0, $bl->endChar - 1);
								break;
							} else {
								if ($line > $bl->startEndLine && $line < $bl->endLine) {
									$bl->text .= $str;
									break;
								}

							}
						}
					}
				}
				$bl->text = trim($bl->text);
				if (empty($bl->arguments)) {
					if (!empty($bl->text)) {
						$this->translations[$this->domain]->add(
							$this->createTranslation($bl->text, $bl->startLine)
						);
					}
				} else {
					foreach ($bl->arguments as $name => $val) {
						if ($name != 'escape') {
							$this->translations[$this->domain]->add(
								$this->createTranslation($val, $bl->startLine)
							);
						}
					}
				}
			}
		}


		public function createTranslation($name, $line, $translate = '', $plural = '')
		{
			$translation = $this->trans[$name] ?? $this->trans[$name] = Translation::create(NULL, $name);
			$f           = str_replace(WT_BASE_PATH, '', $this->FileName);
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

	class TBlock
	{

		const start    = '{t';
		const startEnd = '}';
		const end      = '{/t}';

		public int    $startLine    = -1;
		public int    $startChar    = -1;
		public int    $startEndLine = -1;
		public int    $startEndChar = -1;
		public int    $endLine      = -1;
		public int    $endChar      = -1;
		public string $text         = '';
		public array  $arguments    = [];

		function addArg($name, $value)
		{
			if (is_numeric($name)) {
				$name = (int)$name;
			}
			$this->arguments[$name] = $value;
		}
	}