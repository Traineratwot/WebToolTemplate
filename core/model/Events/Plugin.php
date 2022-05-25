<?php

	namespace model\Events;

	abstract class Plugin
	{
		abstract public function process($data);

		final public function run($data)
		{
			return $this->process($data);
		}
	}