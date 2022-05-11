<?php

	namespace model\cli;
	require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

	use model\cli\commands\CacheCmd;
	use model\cli\commands\CronCmd;
	use model\cli\commands\ErrorCmd;
	use model\cli\commands\localeCmd;
	use model\cli\commands\make\MakeCron;
	use model\cli\commands\make\MakePage;
	use model\cli\commands\make\MakeRest;
	use model\cli\commands\make\MakeTable;
	use model\main\Core;
	use Traineratwot\PhpCli\CLI;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\TypeException;

	Core::init();

	try {
		(new CLI())
			->registerCmd('cache', new CacheCmd())
			->registerCmd('cron', new CronCmd())
			->registerCmd('error', new ErrorCmd())
			->registerCmd('err', new ErrorCmd())
			->registerCmd('lang', new localeCmd())
			->registerCmd('locale', new localeCmd())
			->registerCmd('makeCron', new MakeCron())
			->registerCmd('makeTable', new MakeTable())
			->registerCmd('makeRest', new MakeRest())
			->registerCmd('makeAjax', new MakeRest())
			->registerCmd('makePage', new MakePage())
			->run()
		;
	} catch (TypeException $e) {
		Console::failure($e->getMessage());
		exit($e->getCode());
	}