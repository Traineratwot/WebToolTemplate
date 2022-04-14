<?php

	namespace ajax;

	use model\page\Page;
	use model\page\Ajax;

	class Render extends Ajax
	{
		function process()
		{
			$alias = strip_tags($this->PUT['alias']);
			$data  = $this->PUT['data'];
			if ($alias) {
				$data = (array)$data;
				return Page::chunk($alias, $data);
			}
			return $this->failure($alias, $data);
		}
	}

	return 'render';