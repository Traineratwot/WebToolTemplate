<?php

	namespace core\model;
	require_once realpath(WT_MODEL_PATH . 'core.php');
	require_once realpath(WT_MODEL_PATH . 'postFiles.php');
	require_once realpath(WT_MODEL_PATH . 'errors.php');
	require_once realpath(WT_VENDOR_PATH . 'autoload.php');
	$classes = scandir(WT_CLASSES_PATH);
	foreach ($classes as $class) {
		$p = realpath(WT_CLASSES_PATH . $class);
		if (file_exists($p) and is_file($p)) {
			require_once $p;
		}
	}

	class Console
	{
		public const foreground_colors
			= [
				'black'        => '0;30',
				'dark_gray'    => '1;30',
				'blue'         => '0;34',
				'light_blue'   => '1;34',
				'green'        => '0;32',
				'light_green'  => '1;32',
				'cyan'         => '0;36',
				'light_cyan'   => '1;36',
				'red'          => '0;31',
				'light_red'    => '1;31',
				'purple'       => '0;35',
				'light_purple' => '1;35',
				'brown'        => '0;33',
				'yellow'       => '1;33',
				'light_gray'   => '0;37',
				'white'        => '1;37',
			];
		public const background_colors
			= [
				'black'      => '40',
				'red'        => '41',
				'green'      => '42',
				'yellow'     => '43',
				'blue'       => '44',
				'magenta'    => '45',
				'cyan'       => '46',
				'light_gray' => '47',
			];

		// Returns colored string

		/**
		 * @param $string
		 * @param $foreground_color
		 * @param $background_color
		 * @return mixed|string
		 */
		public static function getColoredString($string, $foreground_color = NULL, $background_color = NULL)
		{
			if (PHP_SAPI == 'cli') {
				$colored_string = "";
				// Check if given foreground color found
				if (isset(Console::foreground_colors[$foreground_color])) {
					$colored_string .= "\033[" . Console::foreground_colors[$foreground_color] . "m";
				}
				// Check if given background color found
				if (isset(Console::background_colors[$background_color])) {
					$colored_string .= "\033[" . Console::background_colors[$background_color] . "m";
				}
				// Add string and end coloring
				$colored_string .= $string . "\033[0m";
				return $colored_string;
			} else {
				return $string;
			}
		}

		// Returns all foreground color names
		public static function getForegroundColors()
		{
			return array_keys(Console::foreground_colors);
		}

		// Returns all background color names
		public static function getBackgroundColors()
		{
			return array_keys(Console::background_colors);
		}

		// Ask user, Return user prompt
		public static function prompt($prompt = "", $hidden = FALSE)
		{
			if (WT_TYPE_SYSTEM !== 'nix') {
				$prompt   = strtr($prompt, [
					'"' => "'",
				]);
				$vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
				file_put_contents(
					$vbscript, 'wscript.echo(InputBox("'
							 . addslashes($prompt)
							 . '", "", ""))');
				$command  = "cscript //nologo " . escapeshellarg($vbscript);
				$password = rtrim(shell_exec($command));
				unlink($vbscript);
				return $password;
			} else {
				$prompt  = strtr($prompt, [
					"'" => '"',
				]);
				$hidden  = $hidden ? '-s' : '';
				$command = "/usr/bin/env bash -c 'echo OK'";
				if (rtrim(shell_exec($command)) !== 'OK') {
					trigger_error("Can't invoke bash");
					return;
				}
				$command  = "/usr/bin/env bash -c {$hidden} 'read  -p \""
					. addslashes($prompt . ' ')
					. "\" answer && echo \$answer'";
				$password = rtrim(shell_exec($command));
				echo "\n";
				return $password;
			}
		}

		// Returns Red text
		public static function failure($t)
		{
			$t = ucfirst($t);
			echo Console::getColoredString($t, 'red') . PHP_EOL;
		}

		// Returns Yellow text
		public static function warning($t)
		{
			$t = ucfirst($t);
			echo Console::getColoredString($t, 'yellow') . PHP_EOL;
		}

		// Returns Green text
		public static function success($t)
		{
			$t = ucfirst($t);
			echo Console::getColoredString($t, 'green') . PHP_EOL;
		}
	}

	/** @var Console $Console */
	$console = new Console();
	/** @var Core $core */
	$core = new Core();