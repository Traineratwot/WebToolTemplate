<?php

	namespace model\cli\types;

	use Exception;
	use model\cli\commands\FindPlugins;
	use Traineratwot\Cache\Cache;
	use Traineratwot\Cache\CacheException;
	use Traineratwot\PhpCli\types\TEnum;

	class PluginsEnum extends TEnum
	{
		/**
		 * @throws CacheException
		 */
		public function enums()
		{
			$plugins = Cache::call('pluginsList', function () {
				try {
					return (new FindPlugins())->run(TRUE);
				} catch (Exception $e) {

				}
			},                     600, 'cli');
			return array_keys($plugins);
		}
	}