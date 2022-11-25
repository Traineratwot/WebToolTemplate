<?php

	namespace core\model\Events\plugins;

	use model\page\Page;

	interface AfterRenderOut
	{
		public function process(string|false $content, Page $page);
	}
