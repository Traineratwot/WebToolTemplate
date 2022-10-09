<?php

	namespace model\cli\commands;

	use model\cli\types\LocaleEnum;
	use model\locale\PoUpdate;
	use model\main\Core;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\TypeException;
	use Traineratwot\PhpCli\types\TString;
	use traits\validators\ExceptionValidate;

	class LocaleCmd extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "🌎 Управление локализацией";
		}

		/**
		 * @throws TypeException|ExceptionValidate
		 */
		public function run()
		{
			$action = $this->getArg('action');
			if ($action === 'list') {
				if (Config::get('TYPE_SYSTEM') === 'nix') {
					exec("locale -a", $out);
					Console::success('Installed locale:');
					foreach ($out as $l) {
						Console::info(" $l");
					}
				} else {
					Console::failure("Windows don`t have command to get locale, use template 'XX.utf8' where XX - lang code");
				}
			}
			if ($action === 'update') {
				$lang = $this->getArg('lang');
				if (!$lang) {
					throw new TypeException("missing lang");
				}
				if (Config::get('TYPE_SYSTEM') === 'nix') {
					exec("locale -a|grep {$lang}", $out);
					if (empty($out)) {
						throw new TypeException("please install locales");
					}
					$t = FALSE;
					foreach ($out as $locale) {
						if ($locale === $lang) {
							$t = TRUE;
							break;
						}
					}
					if (!$t) {
						foreach ($out as $l) {
							Console::info(" $l");
						}
						throw new TypeException("Chose lang from that list");
					}
					Console::info('start generator');
					self::localeGenerator($lang);
				} else {
					$newLang = Core::init()->setLocale($lang, FALSE);
					if (strpos($lang, 'utf8') === FALSE) {
						Console::warning("Recommend add '.utf8");
						if (!(int)Console::prompt('Continue with? "' . $lang . '" 1/0')) {
							return;
						}
					}
					if (!$newLang) {
						Console::failure("can't set locale '$lang' ");
					} elseif ($lang === $newLang) {
						Console::success('start generator');
						self::localeGenerator($newLang);
					} else {
						Console::warning("can't set locale '$lang' but set '$newLang' ");
						if (!(int)Console::prompt('Continue with "' . $newLang . '" ? 1/0')) {
							return;
						}
						Console::success('start generator');
						self::localeGenerator($newLang);
					}
				}
			}
		}

		public static function localeGenerator($lang)
		{
			require_once(Config::get('MODEL_PATH') . 'locale/PoUpdate.php');
			(new PoUpdate())->run($lang);
			Console::success('ok');
			exit();
		}

		public function setup()
		{
			$this->registerParameter('action', 1, LocaleEnum::class, "list - выведет список поддерживемых системой языов, update - создает структуру языковых файла и.или обновляет индекс ключей");
			$this->registerParameter('lang', 0, TString::class, "Язык");
		}
	}