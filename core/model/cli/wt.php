<?php

	namespace model\cli;
	require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

	use core\model\cli\commands\FindPlugins;
	use model\cli\commands\CacheCmd;
	use model\cli\commands\CronCmd;
	use model\cli\commands\ErrorCmd;
	use model\cli\commands\LocaleCmd;
	use model\cli\commands\make\MakeCron;
	use model\cli\commands\make\MakePage;
	use model\cli\commands\make\MakePlugin;
	use model\cli\commands\make\MakeRest;
	use model\cli\commands\make\MakeTable;
	use model\main\Core;
	use Traineratwot\PhpCli\CLI;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\TypeException;

	Core::init();

	try {
		(new CLI())
			->registerCmd('Cache', new CacheCmd())
			->registerCmd('Cron', new CronCmd())
			->registerCmd('Error', new ErrorCmd())
			->registerCmd('Err', new ErrorCmd())
			->registerCmd('Lang', new LocaleCmd())
			->registerCmd('Locale', new LocaleCmd())
			->registerCmd('MakeCron', new MakeCron())
			->registerCmd('MakeTable', new MakeTable())
			->registerCmd('MakeRest', new MakeRest())
			->registerCmd('MakeAjax', new MakeRest())
			->registerCmd('MakePage', new MakePage())
			->registerCmd('MakePlugin', new MakePlugin())
			->registerCmd('FindPlugins', new FindPlugins())
			->run()
		;
	} catch (TypeException $e) {
		Console::failure($e->getMessage());
		exit($e->getCode());
	}