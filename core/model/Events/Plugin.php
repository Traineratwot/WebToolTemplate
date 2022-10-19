<?php

	namespace model\Events;

	abstract class Plugin
	{
		final public function run(...$args)
		{
			return $this->process(...$args);
		}
	}