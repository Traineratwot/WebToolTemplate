<?php

	namespace model\helper;

	use Exception;
	use model\main\Utilities;

	class CsvTable
	{
		/* @var string chr(239) . chr(187) . chr(191) */
		public $utf8bom;
		/* @var string Charset */
		public $inputCharset;
		public $cache;
		public $currentRow  = -1;
		public $clearRegexp = '@[^[:ascii:]A-я]+@u';
		public $currentCol  = -1;
		/* @var array */
		public $matrix = [];
		public $limit  = FALSE;
		/* @var string output csv string */
		protected $csv;
		/* @var string output html string */
		protected $html;
		/* @var array */
		protected $head       = [];
		protected $_head      = [];
		protected $appendType = FALSE;
		protected $str_delimiter;
		protected $line_delimiter;
		protected $escape;
		/**
		 * @var bool|mixed|string
		 */
		private $mode;
		/**
		 * @var bool|mixed|resource
		 */
		private $output_file;
		/**
		 * @var bool|mixed
		 */
		private $_output_file;
		/**
		 * @var array|bool
		 */
		private $limits
			= [
				'l' => ['min' => 30, 'max' => 60],
				's' => ['min' => 80, 'max' => 100],
			];
		private $output;

		/**
		 * UtilitiesCsv constructor
		 *
		 * ###params:
		 *  - inputCharset = 'utf8'
		 *
		 *  - woBom
		 *
		 *  - delimiter = ';'
		 *
		 *  - line_delimiter = \n
		 *
		 *  - mode = default | fast
		 *
		 *  - output_file
		 * @param array $param
		 * @throws Exception
		 */
		public function __construct($param = [])
		{
			$this->inputCharset = $param['inputCharset'] ?? 'utf8';

			$this->utf8bom        = array_key_exists('woBom', $param) ? $param['woBom'] : chr(239) . chr(187) . chr(191);
			$this->str_delimiter  = array_key_exists('delimiter', $param) ? $param['delimiter'] : ';';
			$this->line_delimiter = array_key_exists('line_delimiter', $param) ? $param['line_delimiter'] : "\n";
			$this->escape         = array_key_exists('escape', $param) ? $param['escape'] : '"';
			$this->output_file    = $param['output_file'] ?? NULL;
			$this->mode           = array_key_exists('mode', $param) ? $param['mode'] : 'default';

			if (($this->mode == 'fast') && $this->output_file && $this->utf8bom) {
				fwrite($this->output_file, $this->utf8bom);
			}

		}

		/**
		 * @param array $param
		 * @return $this
		 */
		public function reset(&$param = [])
		{
			$this->inputCharset   = $param['inputCharset'] ?? $this->inputCharset;
			$this->utf8bom        = (isset($param['woBom']) && $param['woBom'] = TRUE) ? NULL : $this->utf8bom;
			$this->str_delimiter  = $param['delimiter'] ?? $this->str_delimiter;
			$this->line_delimiter = $param['line_delimiter'] ?? $this->line_delimiter;
			$this->csv            = NULL;
			$this->html           = NULL;
			$this->matrix         = NULL;
			$this->head           = [];
			$this->appendType     = FALSE;
			return $this;
		}

		/**
		 * add column to csv
		 * @return $this|bool
		 */
		public function addCol()
		{
			if (!$this->appendType || !$this->isEmpty(($this->matrix))) {
				$this->appendType = 'column';
			}
			if ($this->appendType != 'column') {
				return FALSE;
			}
			$args = func_get_args();

			if (count($args) == 1 && is_array($args[0])) {
				$args = $args[0];
			}
			$head    = array_flip($this->_head);
			$isAssoc = Utilities::isAssoc($args);
			foreach ($args as $k => $art) {
				if (!is_string($art) && !is_numeric($art)) {
					$art = NULL;
				} else {
					$art = (string)$art;
				}
				if ($isAssoc) {
					$this->matrix[$head[$k]][] = $art;
				} else {
					$this->matrix[$k][] = $art;
				}
			}

			return $this;
		}

		function isEmpty($var)
		{
			switch (gettype($var)) {
				case "array":
					if (count($var) == 0) {
						return 0;
					}
					break;
				case "string":
					return (trim($var) == '') ? 0 : 1;
				case "NULL":
				case "resource (closed)":
					return 0;
				case "boolean":
				case "integer":
				case "resource":
					return 1;
				default:
					return (int)!empty($var);
			}
			$score = 0;
			foreach ($var as $k => $v) {
				$score += $this->isEmpty($v);
			}
			return !$score;
		}

		/**
		 * @param string|int $x column
		 * @param string|int $y row
		 * @param string|int $value
		 */
		public function setCell($x = 0, $y = 0, $value = '')
		{
			if (!empty($this->head)) {
				switch ($this->appendType) {
					case 'row':
						if (in_array($x, $this->head, TRUE)) {
							$head = array_flip($this->head);
							$x    = $head[$x];
						}
						break;
					case 'column':
						if (in_array($y, $this->head, TRUE)) {
							$head = array_flip($this->head);
							$y    = $head[$y];
						}
						break;
					default:
						return FALSE;
				}
			}
			$x = (int)$x;
			$y = (int)$y;
			for ($i = 0; $i <= $y; $i++) {
				if (!array_key_exists($i, $this->matrix)) {
					$this->matrix[$i] = [];
				}
			}
			for ($i = 0; $i <= $x; $i++) {
				if ($i == $x) {
					$this->matrix[$y][$i] = $value;
				}
				if (!array_key_exists($i, $this->matrix[$y])) {
					$this->matrix[$y][$i] = '';
				}
			}
			return $this;
		}

		/**
		 * @param string|int $x
		 * @param string|int $y
		 * @return bool|mixed
		 */
		public function getCell($x = 0, $y = 0)
		{
			if (!empty($this->head)) {
				switch ($this->appendType) {
					case 'row':
						if (in_array($x, $this->head, TRUE)) {
							$head = array_flip($this->head);
							$x    = $head[$x];
						}
						break;
					case 'column':
						if (in_array($y, $this->head, TRUE)) {
							$head = array_flip($this->head);
							$y    = $head[$y];
						}
						break;
					default:
						return FALSE;
				}
			}
			$x = (int)$x;
			$y = (int)$y;
			if (isset($this->matrix[$y]) && isset($this->matrix[$y][$x])) {
				return $this->matrix[$y][$x];
			}
			return FALSE;
		}

		/**
		 * @throws Exception
		 */
		public function save()
		{
			if ($this->mode != 'fast') {
				throw new Exception('useless in default mod');
				return FALSE;
			}
			if (fclose($this->output_file)) {
				$this->output_file = NULL;
			}
			return $this;
		}

		/**
		 * @param string $cls
		 * @return String
		 */
		public function toHtml($cls = '', $rainbow = FALSE)
		{
			$this->_buildHtmlTable($cls, $rainbow);
			return $this->html;
		}

		/**
		 *generate html table string
		 */
		public function _buildHtmlTable($cls = '', $rainbow = FALSE)
		{
			$this->matrixFix();
			$this->sort();
			$this->html = "<table class=\"$cls\">";
			$len        = [];
			$head       = $this->head;
			$len[]      = count($head);
			foreach ($this->matrix as $row) {
				$len[] = count($row);
			}
			$len = max($len);
			if (!empty($head)) {
				if ($this->appendType === 'row') {
					$this->html .= "<thead><tr data-key='-1'>";
					foreach ($head as $k => $h) {
						$style = '';
						if ($rainbow) {
							$style = 'style="color:' . $this->randomColor(['salt' => $k, 'limits' => $this->limits]) . ';"';
						}
						$_h         = strip_tags($h);
						$_k         = strip_tags($k);
						$this->html .= "<th data-key='$_k' data-value='$_h' $style>$h</th>";
					}
					$this->html .= "</thead></tr>";
				} else {
					foreach ($this->head as $k => $h) {
						if (isset($this->matrix[$k]) && is_array($this->matrix[$k])) {
							array_unshift($this->matrix[$k], $h);
						}
					}
				}
			}
			foreach ($this->matrix as $key => $row) {
				$_row = [];
				for ($i = 0; $i < $len; $i++) {
					$_row[$i] = $row[$i] ?? '';
				}
				if (!$this->isEmpty($_row)) {
					$this->html .= "<tr data-key='$key'>";
					$i          = 0;
					foreach ($row as $k => $r) {
						$style = '';
						if ($rainbow) {
							$style = 'style="color:' . $this->randomColor(['salt' => $k, 'limits' => $this->limits]) . ';"';
						}
						$i++;
						$_r = strip_tags($r);
						$_k = strip_tags($k);
						if ($this->head && $this->appendType === 'column' && $i == 1) {
							$this->html .= "<th data-key='$_k' data-value='$_r' $style>$r</th>";
						} else {
							$this->html .= "<td data-key='$_k' data-value='$_r' $style>$r</td>";
						}
					}
					$this->html .= "</tr>";
				}
			}
			$this->html .= '</table>';
		}

		private function matrixFix()
		{
			$lenCol[] = count($this->head);
			if (is_array($this->matrix)) {
				foreach ($this->matrix as $row) {
					$lenCol[] = count($row);
				}
				$lenCol = max($lenCol);
				foreach ($this->matrix as $k => $row) {
					for ($i = 0; $lenCol > $i; $i++) {
						if (!isset($row[$i])) {
							$this->matrix[$k][$i] = NULL;
						}
					}
				}
			}
		}

		private function sort()
		{
			ksort($this->head);
			foreach ($this->matrix as $k => $v) {
				ksort($this->matrix[$k]);
			}
		}

		/**
		 * @param array $options
		 * @return array|string
		 * @throws Exception
		 */
		public function randomColor($options = [])
		{
			$options = array_merge([
									   'limits' => [],
									   'salt'   => FALSE,
									   'format' => 'hsl',
									   'type'   => 'css',
								   ], $options);
			if ($options['salt']) {
				if (!is_numeric($options['salt'])) {
					$seed = (int)preg_replace("/[^0-9]/", '', md5($options['salt']));
				} else {
					$seed = (int)($options['salt'] * 100);
				}
				mt_srand($seed);
			}

			if (isset($this->cache[__FUNCTION__][$options['format']][$options['type']][$options['salt']])) {
				return $this->cache[__FUNCTION__][$options['format']][$options['type']][$options['salt']];
			}
			if (isset($options['limits']['l'])) {
				if (isset($options['limits']['l']['max'])) {
					$options['limits']['l']['max'] = min($options['limits']['l']['max'], 100);
					$options['limits']['l']['max'] = max($options['limits']['l']['max'], 0);
				}
				if (isset($options['limits']['l']['min'])) {
					$options['limits']['l']['min'] = min($options['limits']['l']['min'], 100);
					$options['limits']['l']['min'] = max($options['limits']['l']['min'], 0);
				}
			}
			if (isset($options['limits']['s'])) {
				if (isset($options['limits']['l']['max'])) {
					$options['limits']['s']['max'] = min($options['limits']['s']['max'], 100);
					$options['limits']['s']['max'] = max($options['limits']['s']['max'], 0);
				}
				if (isset($options['limits']['l']['min'])) {
					$options['limits']['s']['min'] = min($options['limits']['s']['min'], 100);
					$options['limits']['s']['min'] = max($options['limits']['s']['min'], 0);
				}
			}
			if (isset($options['limits']['h'])) {
				if (isset($options['limits']['l']['max'])) {
					$options['limits']['h']['max'] = min($options['limits']['h']['max'], 360);
					$options['limits']['h']['max'] = max($options['limits']['h']['max'], 0);
				}
				if (isset($options['limits']['l']['min'])) {
					$options['limits']['h']['min'] = min($options['limits']['h']['min'], 360);
					$options['limits']['h']['min'] = max($options['limits']['h']['min'], 0);
				}
			}

			$h = random_int($options['limits']['h']['min'] ?? 0, $options['limits']['h']['max'] ?? 360);
			$s = random_int($options['limits']['s']['min'] ?? 0, $options['limits']['s']['max'] ?? 100);
			$l = random_int($options['limits']['l']['min'] ?? 0, $options['limits']['l']['max'] ?? 100);
			switch (mb_strtolower($options['format'])) {
				case 'hex';
					$this->hsl2rgb($h, $s, $l);
					return $this->rgb2hex($h, $s, $l);
				case 'rgb';
					$this->hsl2rgb($h, $s, $l);
					if (mb_strtolower($options['type']) == 'css') {

						return "rgb($h, $s, $l)";
					}
					return [
						'r' => $h,
						'g' => $s,
						'b' => $l,
					];
				default:

					if (mb_strtolower($options['type']) == 'css') {
						$this->cache[__FUNCTION__][$options['format']][$options['type']][$options['salt']] = "hsl($h, $s%, $l%)";
						return "hsl($h, $s%, $l%)";
					}
					$this->cache[__FUNCTION__][$options['format']][$options['type']][$options['salt']] = [
						'h' => $h,
						's' => $s,
						'l' => $l,
					];
					return [
						'h' => $h,
						's' => $s,
						'l' => $l,
					];
			}

		}

		/**
		 * @param $rH
		 * @param $gS
		 * @param $bL
		 * @return array
		 */
		public function hsl2rgb(&$rH, &$gS, &$bL)
		{

			$c = (1 - abs(2 * $bL - 1)) * $gS;
			$x = $c * (1 - abs(fmod(($rH / 60), 2) - 1));
			$m = $bL - ($c / 2);

			if ($rH < 60) {
				$r = $c;
				$g = $x;
				$b = 0;
			} elseif ($rH < 120) {
				$r = $x;
				$g = $c;
				$b = 0;
			} elseif ($rH < 180) {
				$r = 0;
				$g = $c;
				$b = $x;
			} elseif ($rH < 240) {
				$r = 0;
				$g = $x;
				$b = $c;
			} elseif ($rH < 300) {
				$r = $x;
				$g = 0;
				$b = $c;
			} else {
				$r = $c;
				$g = 0;
				$b = $x;
			}

			$rH = floor(($r + $m) * 255);
			$gS = floor(($g + $m) * 255);
			$bL = floor(($b + $m) * 255);

			return [$rH, $gS, $bL];
		}

		public function rgb2hex(&$R, &$G, &$B)
		{

			$R = dechex($R);
			if (strlen($R) < 2) {
				$R = '0' . $R;
			}

			$G = dechex($G);
			if (strlen($G) < 2) {
				$G = '0' . $G;
			}

			$B = dechex($B);
			if (strlen($B) < 2) {
				$B = '0' . $B;
			}

			return '#' . $R . $G . $B;
		}

		/**
		 * @param string $cls
		 * @return String
		 */
		public function toHtmlTable($cls = '', $rainbow = FALSE)
		{
			$this->_buildHtmlTable($cls, $rainbow);
			return $this->html;
		}

		/**
		 * @param string $cls
		 * @param string $delimiter
		 * @param string $item UL, OL, LI и DL
		 * @return String
		 */
		public function toHtmlList($cls = '', $delimiter = '; ', $item = 'li', $rainbow = FALSE)
		{
			$this->_buildHtmlList($cls, $delimiter, $item, $rainbow);
			return $this->html;
		}

		/**
		 * @param string $cls
		 * @param string $delimiter
		 * @param string $item li|ol
		 */
		public function _buildHtmlList($cls = '', $delimiter = ' ', $item = 'li', $rainbow = FALSE)
		{
			$this->matrixFix();
			$this->sort();
			$this->html = "<ul class=\"$cls\">";
			$len        = [];
			$head       = $this->head;
			$len[]      = count($head);
			foreach ($this->matrix as $row) {
				$len[] = count($row);
			}
			$len = max($len);
			if (!empty($head)) {
				if ($this->appendType == 'row') {
					$this->html .= "<$item>";
					foreach ($head as $k => $h) {
						$style = '';
						if ($rainbow) {
							$style = 'style="color:' . $this->randomColor(['salt' => $k, 'limits' => $this->limits]) . ';"';
						}
						$this->html .= "<strong $style>$h</strong>" . $delimiter;
					}
					$this->html .= "</$item>";
				} else {
					foreach ($this->head as $k => $h) {
						array_unshift($this->matrix[$k], $h);
					}
				}
			}
			foreach ($this->matrix as $key => $row) {
				$_row = [];
				for ($i = 0; $i < $len; $i++) {
					$_row[$i] = $row[$i] ?? '';
				}
				if (!$this->isEmpty($_row)) {
					$this->html .= "<$item>";
					$i          = 0;
					foreach ($row as $k => $r) {
						$style = '';
						if ($rainbow) {
							$style = 'style="color:' . $this->randomColor(['salt' => $k, 'limits' => $this->limits]) . ';"';
						}
						$i++;
						if ($this->head && $this->appendType == 'column' && $i == 1) {
							$this->html .= "<strong $style>$r</strong>" . $delimiter;
						} else {
							$this->html .= "<span $style>$r</span>" . $delimiter;
						}
					}
					$this->html .= "</$item>";
				}
			}
			$this->html .= '</ul>';
		}

		/**
		 * @return array
		 */
		public function toArray()
		{
			return $this->matrix;
		}

		public function getRow($currentCol = FALSE, $assoc = FALSE)
		{
			if ($currentCol !== FALSE) {
				$this->currentCol = $currentCol;
			}
			if ($this->currentRow == -1) {
				$this->currentRow++;
				return $this->head;
			}
			$row = $this->matrix[$this->currentRow] ?? FALSE;
			if ($row) {
				$this->currentRow++;
				$_row = [];
				if ($assoc) {
					foreach ($row as $k => $v) {
						$_row[$this->head[$k]] = $v;
					}
				} else {
					foreach ($row as $k => $v) {
						$_row[$k] = $v;
					}
				}
				return $_row;
			}

			$this->currentRow = 0;
			return FALSE;
		}

		public function getAssoc($type = 'row')
		{
			$this->sort();
			$response = [];
			switch ($type) {
				case 'row':
					$key = NULL;
					foreach ($this->matrix as $row) {
						foreach ($row as $i => $cell) {
							if ($i == 0) {
								$key = $cell;
								continue;
							}
							if ($key) {
								if (count($row) > 2) {
									if (empty($response[$key])) {
										$response[$key] = [$cell];
									} else {
										$response[$key][] = $cell;
									}
								} else {
									$response[$key] = $cell;
								}
							} else {
								return FALSE;
							}
						}
					}
					break;
				case 'col':
				case 'head':
					$s = $this->currentCol;
					foreach ($this->head as $key => $head) {
						$col             = $this->getCol($key);
						$response[$head] = $col;
					}
					$this->currentCol = $s;
					break;
			}
			if (!empty($response)) {
				return $response;
			}
			return FALSE;
		}

		public function getCol($currentCol = FALSE)
		{
			if ($currentCol !== FALSE) {
				$this->currentCol = $currentCol;
			}
			if ($this->currentCol == -1) {
				$this->currentCol++;
				return $this->head;
			}
			$col = [];
			foreach ($this->matrix as $v) {
				if (isset($v[$this->currentCol])) {
					$col[] = $v[$this->currentCol];
				}
			}
			if ($col) {
				$this->currentCol++;
				return $col;
			}

			$this->currentCol = 0;
			return FALSE;
		}

		/**
		 * @return string "csvString"|string
		 */
		final public function __toString()
		{
			return $this->toCsv();
		}

		/**
		 * @return String
		 */
		public function toCsv()
		{
			$this->_buildCsv();
			return $this->csv;
		}

		/**
		 *generate csv string
		 */
		public function _buildCsv()
		{
			$this->matrixFix();
			$this->sort();
			$this->csv = $this->utf8bom;
			$len       = [];
			$head      = $this->head;
			$len[]     = count($head);
			foreach ($this->matrix as $row) {
				$len[] = count($row);
			}
			foreach ($head as $i => $v) {
				$head[$i] = $this->escape . strip_tags($v) . $this->escape;
			}
			$len = max($len);
			if (!empty($head)) {
				if ($this->appendType == 'row') {
					$this->csv .= implode($this->str_delimiter, $head);
				} else {
					foreach ($this->head as $k => $h) {
						array_unshift($this->matrix[$k], $h);
					}
				}
			}
			foreach ($this->matrix as $key => $row) {
				$_row = [];
				for ($i = 0; $i < $len; $i++) {
					$_row[$i] = (isset($row[$i])) ? $this->escape . $row[$i] . $this->escape : '';
				}
				if (!$this->isEmpty($_row)) {
					$this->csv .= $this->line_delimiter;
					$this->csv .= implode($this->str_delimiter, $_row);
				}
			}
		}

		/**
		 * @param $name
		 * @return bool
		 */
		final public function __isset($name)
		{
			return TRUE;
		}

		/**
		 * @param $name
		 * @return false|array
		 */
		final public function __get($name)
		{
			switch ($name) {
				case 'matrix':
					return $this->matrix;

				default:
					return FALSE;

			}
		}

		/**
		 * @param $name
		 * @param $value
		 * @return $this|false
		 * @throws Exception
		 */
		final public function __set($name, $value)
		{
			switch ($name) {
				case 'matrix':
					$this->matrix = $value;
					break;
				case 'csv':
					return $this->readCsv($value);
			}
			return FALSE;
		}

		/**
		 * @param resource|string $source
		 * @return $this|false
		 * @throws Exception
		 * @filesource
		 */
		public function readCsv($source, $limit = 0)
		{
			switch (gettype($source)) {
				case 'string':
					if (!$this->strTest($source, "\n", [$this->line_delimiter, $this->str_delimiter]) && file_exists($source)) {
						$source = @fopen($source, 'rb');
						return $this->_readCsvResource($source, $limit);
					}

					return $this->_readCsvString($source, $limit);
				case'resource':
					return $this->_readCsvResource($source, $limit);
				default:
					return FALSE;
			}
		}

		public function strTest()
		{
			$score = 0;
			$args  = func_get_args();
			$str   = (string)array_shift($args);
			foreach ($args as $arg) {
				switch (gettype($arg)) {
					case 'string':
					case 'integer':
						$score += (int)(strpos($str, (string)$arg) !== FALSE);
						break;
					case 'array':
						$sc_ = 0;
						foreach ($arg as $a) {
							$sc_ += (int)(strpos($str, (string)$a) !== FALSE);
						}
						$score += (int)($sc_ === count($arg));
						break;
				}
			}
			$this->output[__FUNCTION__] = $score;
			return $score;
		}

		/**
		 * @param resource $source
		 * @return $this
		 * @throws Exception
		 */
		function _readCsvResource($source, $limit)
		{
			if (is_resource($source)) {
				$i = 0;
				while (($row = fgetcsv($source, 10240, $this->str_delimiter))) {
					$i++;
					if ($limit && $i > $limit) {
						$this->limit = TRUE;
						break;
					}
					foreach ($row as $i2 => $v) {
						$row[$i2] = trim($v, $this->escape);
					}
					if ($i === 1) {
						$this->setHead($row);
						continue;
					}

					$this->addRow($row);

				}
				fclose($source);
			}
			return $this;
		}

		/**
		 * add header for csv
		 * @return $this
		 * @throws Exception
		 */
		public function setHead()
		{
			$args = func_get_args();
			if (count($args) == 1 && is_array($args[0])) {
				$args = $args[0];
			}
			$_args = [];
			foreach ($args as $k => $art) {
				$k   = $this->clearString($k);
				$art = $this->clearString($art);
				if (!is_string($art) && !is_numeric($art)) {
					$args[$k]  = NULL;
					$_args[$k] = NULL;
				} else {
					$_args[$k] = $art;
					$args[$k]  = $art;
				}
			}
			if ($this->mode == 'fast') {
				$this->_head = $_args;
				foreach ($args as $i => $v) {
					$args[$i] = $this->escape . $v . $this->escape;
				}
				$text = implode($this->str_delimiter, $args) . $this->line_delimiter;
				$this->writeFile($text);
			} else {
				$this->head  = $args;
				$this->_head = $_args;
				$this->matrixFix();
			}
			return $this;
		}

		public function clearString($a = '')
		{
			return preg_replace($this->clearRegexp, '', (string)$a);
		}

		/**
		 * @param $text string
		 * @throws Exception
		 */
		private function writeFile($text)
		{
			if (is_resource($this->output_file)) {
				fwrite($this->output_file, $text);
			} elseif ($this->_output_file) {
				file_put_contents($this->_output_file, $text, FILE_APPEND);
			} else {
				throw new Exception('you call "save" too early');
			}
		}

		/**
		 * add row to csv
		 * @return $this|bool
		 * @throws Exception
		 */
		public function addRow()
		{

			if (!$this->appendType && !$this->isEmpty(($this->matrix))) {
				$this->appendType = 'row';
			}

			if ($this->appendType != 'row') {
				return FALSE;
			}
			$args = func_get_args();

			if (empty($this->_head)) {
				return $this->setHead(...$args);
			}

			if (count($args) == 1 && is_array($args[0])) {
				$args = $args[0];
			}

			$head = array_flip($this->_head);

			$isAssoc = Utilities::isAssoc($args);
			$args_   = [];

			foreach ($args as $k => $art) {
				$k   = $this->clearString($k);
				$art = $this->clearString($art);
				if ($isAssoc) {
					if (!is_string($art) && !is_numeric($art)) {
						$args_[$head[$k]] = NULL;
					} else {
						$args_[$head[$k]] = $art;
					}
				} elseif (!is_string($art) && !is_numeric($art)) {
					$args_[$k] = NULL;
				} else {
					$args_[$k] = $art;
				}
			}
			if ($this->mode == 'fast') {
				ksort($args_);
				foreach ($args_ as $i => $v) {
					$args_[$i] = $this->escape . $v . $this->escape;
				}
				$text = implode($this->str_delimiter, $args_) . $this->line_delimiter;
				$this->writeFile($text);
			} else {
				$this->matrix[] = $args_;
			}

			return $this;
		}

		/**
		 * @param string $source
		 * @return $this
		 * @throws Exception
		 */
		function _readCsvString($source, $limit)
		{
			$i = 0;
			//$rows = str_getcsv($source,$this->str_delimiter);
			$rows = explode($this->line_delimiter, $source);
			if (is_array($rows)) {
				foreach ($rows as $row) {
					$i++;
					if ($limit && $i > $limit) {
						$this->limit = TRUE;
						break;
					}
					if (is_array($row)) {
						foreach ($row as $i => $v) {
							$row[$i] = $this->escape . $v . $this->escape;
						}
					}
					$row = explode($this->str_delimiter, $row);
					if ($i === 1) {
						$this->setHead($row);
						continue;
					}
					$this->addRow($row);
				}
			}
			return $this;
		}

		public function rowCount()
		{
			return count($this->matrix);
		}

		public function colCount()
		{
			return count($this->head);
		}
	}