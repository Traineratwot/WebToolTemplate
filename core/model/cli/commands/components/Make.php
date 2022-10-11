<?php

	namespace model\cli\commands\components;

	use model\main\Utilities;


	class Make
	{
		public static function makeAjax(string $componentName, string $name, string $type = 'any')
		: string
		{
			$method = match ($type) {
				'post'  => <<<PHP
	public function POST(){
		//TODO YOU CODE
	}
PHP,
				'get'   => <<<PHP
	public function GET(){
		//TODO YOU CODE
	}
PHP,
				default => <<<PHP
	public function process(){
		//TODO YOU CODE
	}
PHP,
			};
			self::name2class($name, $class, $namespace);
			return <<<PHP
<?php
	namespace components\\{$componentName}\classes\ajax{$namespace};
	
	use model\page\Ajax;
	
	class {$class} extends Ajax
	{
		{$method}
	}
	return {$class}::class;
PHP;
		}

		public static function name2class(string $name, ?string &$class = '', ?string &$namespace = '')
		: string
		{
			$n0    = explode(":::", Utilities::pathNormalize($name, ':::'));
			$class = array_pop($n0);
			$class = ucfirst($class);
			if (count($n0)) {
				$namespace = '\\' . implode('\\', $n0);
			}

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
				$n2[] = ucfirst(strtolower($value));
				$n3[] = ucfirst(strtolower($value));
			}

			return ucfirst(implode('', $n2));
		}

		public static function makePlugin(string $componentName, string $name, string $type = 'any')
		: string
		{
			self::name2class($name, $class, $namespace);
			return <<<PHP
<?php
	namespace components\\{$componentName}\classes\plugins{$namespace};
	use model\Events\Plugin;
	class {$class} extends Plugin
	{
		public function process(\$data)
		{
			//TODO make plugin {$class}
		}
	}
	return {$class}::class;
PHP;
		}

		public static function makePageTpl(string $componentName, string $name, string $template = 'base')
		: string
		{
			if (!$template) {
				$template = 'base.tpl';
			} else {
				$template .= '.tpl';
			}
			return <<<TPL
{*$componentName / $name*}
{extends file='{$template}'}
{block name="head"}

{/block}
{block name='content'}
	
{/block}
TPL;
		}

		public static function makePageClass(string $componentName, string $name)
		: string
		{
			self::name2class($name, $class, $namespace);
			return <<<PHP
<?php

	namespace components\\{$componentName}\\views{$namespace};

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

		public static function makeTable(string $componentName,string $name, string $primaryKey = 'id',&$namespace_class='')
		: string
		{
			$primaryKey = $primaryKey ?: 'id';
			$class      = self::name2class($name, $class);
			$namespace_class ="components\\{$componentName}\classes\\tables{$namespace}\\{$class}";
			return <<<PHP
<?php
	namespace components\\{$componentName}\classes\\tables{$namespace};
	use model\components\ComponentTable;

	/**
	 * Класс для работы с таблицей `$name`
	 * вызывается Core::getObject($class::class)
	 */
	class $class extends ComponentTable
	{
		public \$table = '$name';
		public \$primaryKey = '$primaryKey';
	}
PHP;
		}

		public static function makeClass(string $name, string $category = '')
		: string
		{
			if ($category) {
				$category = '\\' . $category;
			}
			$class = self::name2class($name);
			return <<<PHP
<?php
	namespace components\\{$componentName}\classes{$category};
	use model\\CoreObject;

	/**
	 * вызывается Core::init()->getObject($class::class)
	 */
	class $class extends CoreObject
	{
	
	
	}
PHP;
		}

		public static function makeComponentManifest(string $componentName)
		: string
		{
			return <<<PHP
<?php

	namespace components\\{$componentName};

	use model\components\Manifest;

	class $componentName extends Manifest
	{
		public static function description()
		: string
		{
			return '';
		}

		public static function getComposerPackage()
		: array
		{
			return [];
		}
	}
PHP;
		}

		public static function pathFileUcFirst($path)
		: string
		{
			$file = basename($path);
			return str_ireplace($file, ucfirst($file), $path);
		}


	}