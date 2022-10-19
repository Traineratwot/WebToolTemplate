<?php

	namespace core\model\Events\plugins;

	use model\page\Page;

	interface BeforeRender
	{
		public function process(Page $page);
	}
