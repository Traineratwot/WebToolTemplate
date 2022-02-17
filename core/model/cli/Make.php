<?php

	namespace model\cli;

	class Make
	{
		public static function makeAjax($name, $type = 'any')
		{
			$method = '';
			switch ($type) {
				case 'post':
					$method = <<<PHP
	function POST(){
		//TODO YOU CODE
	}
PHP;
					break;
				case 'get':
					$method = <<<PHP
	function GET(){
		//TODO YOU CODE
	}
PHP;
					break;
				default:
					$method = <<<PHP
	function process(){
		//TODO YOU CODE
	}
PHP;
					break;
			}
			$class = \model\Make::name2class($name);
			$code  = <<<PHP
<?php
	namespace ajax;
	use model\Ajax;
	class {$class} extends Ajax
	{
		{$method}
	}
	return '{$class}';
PHP;
			return $code;
		}

		public static function makePageTpl($name, $template = 'base')
		{
			if (!$template) {
				$template = 'base.tpl';
			} else {
				$template .= '.tpl';
			}
			$code = <<<TPL
{extends file='{$template}'}
{block name="head"}

{/block}
{block name='content'}
	
{/block}
TPL;
			return $code;
		}

		public static function makePageClass($name, $template = 'base')
		{
			$class = Make::name2class($name);
			$code  = <<<PHP
<?php

	namespace page;

	use model\Err;
	use model\Page;

	class {$class} extends Page
	{
		public \$alias = '$name';
		public \$title = '$name';

		public function beforeRender(){

		}
	}

	return '{$class}';
PHP;
			return $code;
		}

		public static function name2class($name)
		{
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
			}
			$class = ucfirst(implode('', $n2));
			return $class;
		}

		public static function makeTemplate($name, $template = 'base')
		{
		}

		public static function makeTable($name, $primaryKey = 'id')
		{
			$primaryKey = $primaryKey ?: 'id';
			$class      = Make::name2class($name);
			$code       = <<<PHP
<?php

	namespace \classes\\table;
	use core\\model\\bdObject;

	/**
	 * Класс для работы с таблицей `$name`
	 * вызывается core::getObject('$class')
	 */
	class $class extends bdObject
	{
		public \$table = '$name';
		public \$primaryKey = '$primaryKey';
	}
PHP;
			return $code;
		}
	}