<?php

	namespace model\Events;

	abstract class Plugin
	{
		final public function run($data)
		{
			return $this->process($data);
		}

		abstract public function process($data);
	}