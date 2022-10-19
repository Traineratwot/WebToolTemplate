<?php

	namespace core\model\Events\plugins;

	use model\page\Page;

	interface AfterRender
	{
		public function process(string|false $content, Page $page);
	}
