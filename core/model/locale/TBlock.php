<?php

	namespace model\locale;

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