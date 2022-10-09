<?php

	namespace model\cli;
	require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

	use model\cli\commands\components\Create;
	use model\cli\commands\components\Install;
	use model\cli\commands\components\make\MakePage;
	use model\cli\commands\components\make\MakePlugin;
	use model\cli\commands\components\make\MakeRest;
	use model\cli\commands\components\Package;
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
			->registerCmd('create', new Create())
			->run()
		;
	} catch (TypeException $e) {
		Console::failure($e->getMessage());
		exit($e->getCode());
	}