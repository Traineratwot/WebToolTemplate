<?php

	namespace ajax;

	use model\page\Ajax;
	use model\page\Page;

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

	return Render::class;