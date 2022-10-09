<?php

	namespace core\model\cli\types;

	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\types\TEnum;

	class ComponentsEnum extends TEnum
	{
		public function enums()
		{
			$response = [];
			$dirs     = scandir(Config::get('COMPONENTS_PATH'));
			if (!empty($dirs)) {
				foreach ($dirs as $key => $dir) {
					if ($dir === "." || $dir === "..") {
						continue;
					}
					$p = Config::get('COMPONENTS_PATH') . $dir;
					if (is_dir($p)) {
						$response[] = $dir;
					}
				}
			}
			return $response;
		}
	}