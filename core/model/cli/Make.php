<?php

	namespace model\cli;

	use model\main\Core;
	use model\main\Utilities;


	class Make
	{
		public static function makeAjax($name, $type = 'any')
		{
			$method = '';
			switch ($type) {
				case 'post':
					$method = <<<PHP
	public function POST(){
		//TODO YOU CODE
	}
PHP;
					break;
				case 'get':
					$method = <<<PHP
	public function GET(){
		//TODO YOU CODE
	}
PHP;
					break;
				default:
					$method = <<<PHP
	public function process(){
		//TODO YOU CODE
	}
PHP;
					break;
			}
			self::name2class($name, $class, $namespace);
			return <<<PHP
<?php
	namespace ajax{$namespace};
	use model\page\Ajax;
	class {$class} extends Ajax
	{
		{$method}
	}
	return {$class}::class;
PHP;
		}

		public static function name2class($name, &$class = '', &$namespace = '')
		{
			$n0        = explode(":::", Utilities::pathNormalize($name, ':::'));
			$class     = array_pop($n0);
			$namespace = '\\' . implode('\\', $n0);

			$name = preg_replace("@([A-Z])@", "_$1", $name);
			$name = strtr($name, [
				'\\' => '_',
				'/'  => '_',
				'-'  => '_',
				' '  => '_',
				'*'  => '_',
				'.'  => '_',
				'+'  => '_',
			]);
			$n    = explode("_", $name);
			$n2   = [];
			foreach ($n as $value) {
				$n2[] = ucfirst(mb_strtolower($value));
				$n3[] = ucfirst(mb_strtolower($value));
			}

			return ucfirst(implode('', $n2));
		}

		public static function makePageTpl($name, $template = 'base')
		{
			if (!$template) {
				$template = 'base.tpl';
			} else {
				$template .= '.tpl';
			}
			return <<<TPL
{*$name*}
{extends file='{$template}'}
{block name="head"}

{/block}
{block name='content'}
	
{/block}
TPL;
		}

		public static function makePageClass($name)
		{
			self::name2class($name, $class, $namespace);
			return <<<PHP
<?php

	namespace page{$namespace};

	use model\page\Page;

	class {$class} extends Page
	{
		public \$title = '$name';

		public function beforeRender(){

		}
	}

	return {$class}::class;
PHP;
		}

		public static function makeTable($name, $primaryKey = 'id')
		{
			$primaryKey = $primaryKey ?: 'id';
			$class      = self::name2class($name, $class);
			return <<<PHP
<?php
	namespace tables;
	use model\\main\\BdObject;

	/**
	 * Класс для работы с таблицей `$name`
	 * вызывается Core::getObject($class::class)
	 */
	class $class extends BdObject
	{
		public \$table = '$name';
		public \$primaryKey = '$primaryKey';
	}
PHP;
		}

		public static function makeClass($name, $category = '')
		{
			if ($category) {
				$category = '\\' . $category;
			}
			$class = self::name2class($name);
			return <<<PHP
<?php
	namespace classes{$category};
	use model\\CoreObject;

	/**
	 * вызывается Core::init()->getObject($class::class)
	 */
	class $class extends CoreObject
	{
	
	
	}
PHP;
		}

		public static function makeCron($name = '')
		{
			$class = self::name2class(basename($name));
			return <<<PHP
<?php
	namespace cron;
	use model\main\Core;
	use model\main\CoreObject;
	class $class extends CoreObject
	{
		function process(){
			//TODO: process
		}
	}
	\$core = Core::init();
	(new $class(\$core))->process();
PHP;
		}
	}