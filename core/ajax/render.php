<?php

	namespace core\ajax;

	use core\model\Ajax;
	use core\model\Page;

	class Render extends Ajax
	{
		function process()
		{
			$alias = strip_tags($this->PUT['alias']);
			$data  = $this->PUT['data'];
			if ($alias) {
				$data = (array)$data;
				return Page::modifier_chunk($alias, $data);
			}
			return $this->failure($alias, $data);
		}
	}

	return 'render';