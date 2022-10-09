<?php

	namespace model\cli;
	require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

	use core\model\cli\commands\components\Init;
	use core\model\cli\commands\components\Install;
	use core\model\cli\commands\components\make\MakePage;
	use core\model\cli\commands\components\make\MakePlugin;
	use core\model\cli\commands\components\make\MakeRest;
	use core\model\cli\commands\components\Package;
	use model\cli\commands\CacheCmd;
	use model\main\Core;
	use Traineratwot\PhpCli\CLI;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\TypeException;

	Core::init();

	try {
		(new CLI())
			->registerCmd('Cache', new CacheCmd())
			->registerCmd('Install', new Install())
			->registerCmd('Package', new Package())
			->registerCmd('makeAjax', new MakeRest())
			->registerCmd('makePlugin', new MakePlugin())
			->registerCmd('makePage', new MakePage())
			->registerCmd('create', new Init())
			->run()
		;
	} catch (TypeException $e) {
		Console::failure($e->getMessage());
		exit($e->getCode());
	}