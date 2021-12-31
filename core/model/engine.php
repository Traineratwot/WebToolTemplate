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
		private $foreground_colors = [];
		private $background_colors = [];

		public function __construct()
		{
			// Set up shell colors
			$this->foreground_colors['black']        = '0;30';
			$this->foreground_colors['dark_gray']    = '1;30';
			$this->foreground_colors['blue']         = '0;34';
			$this->foreground_colors['light_blue']   = '1;34';
			$this->foreground_colors['green']        = '0;32';
			$this->foreground_colors['light_green']  = '1;32';
			$this->foreground_colors['cyan']         = '0;36';
			$this->foreground_colors['light_cyan']   = '1;36';
			$this->foreground_colors['red']          = '0;31';
			$this->foreground_colors['light_red']    = '1;31';
			$this->foreground_colors['purple']       = '0;35';
			$this->foreground_colors['light_purple'] = '1;35';
			$this->foreground_colors['brown']        = '0;33';
			$this->foreground_colors['yellow']       = '1;33';
			$this->foreground_colors['light_gray']   = '0;37';
			$this->foreground_colors['white']        = '1;37';

			$this->background_colors['black']      = '40';
			$this->background_colors['red']        = '41';
			$this->background_colors['green']      = '42';
			$this->background_colors['yellow']     = '43';
			$this->background_colors['blue']       = '44';
			$this->background_colors['magenta']    = '45';
			$this->background_colors['cyan']       = '46';
			$this->background_colors['light_gray'] = '47';
		}

		// Returns colored string
		public function getColoredString($string, $foreground_color = NULL, $background_color = NULL)
		{
			$colored_string = "";

			// Check if given foreground color found
			if (isset($this->foreground_colors[$foreground_color])) {
				$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
			}
			// Check if given background color found
			if (isset($this->background_colors[$background_color])) {
				$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
			}

			// Add string and end coloring
			$colored_string .= $string . "\033[0m";

			return $colored_string;
		}

		// Returns all foreground color names
		public function getForegroundColors()
		{
			return array_keys($this->foreground_colors);
		}

		// Returns all background color names
		public function getBackgroundColors()
		{
			return array_keys($this->background_colors);
		}

		public function prompt($prompt = "", $hidden = FALSE)
		{
			if (WT_TYPE_SYSTEM !== 'nix') {
				$vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
				file_put_contents(
					$vbscript, 'wscript.echo(InputBox("'
							 . addslashes($prompt)
							 . '", "", "' . $prompt . '"))');
				$command  = "cscript //nologo " . escapeshellarg($vbscript);
				$password = rtrim(shell_exec($command));
				unlink($vbscript);
				return $password;
			} else {
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
	}
	/** @var Console $Console */
	$console = new Console();
	/** @var Core $core */
	$core = new Core();