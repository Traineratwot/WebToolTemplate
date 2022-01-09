<?php

	namespace core\model;

	use Exception;

	class PostFile
	{
		public $name     = NULL;
		public $path     = NULL;
		public $ext      = NULL;
		public $fullName = NULL;
		public $type     = NULL;
		/**
		 * @var int
		 */
		public $size;
		public $content;
		/**
		 * @var bool|Csv
		 */
		public $csv;
		/**
		 * @var bool
		 */
		private $saved = FALSE;
		/**
		 * @var mixed
		 */
		private $error;


		/**
		 * PostFile constructor.
		 * @param array $data
		 * @throws Exception
		 */
		public function __construct($data)
		{
			$this->data = $data;
			if (!isset($data['name']) or !isset($data['tmp_name'])) {
				throw new Exception('name or path not found');
			} elseif ($data['error'] != UPLOAD_ERR_OK) {
				$this->error = $data['error'];
				throw new Exception('upload error: "' . $data['error'] . '"');
			} else {
				$this->name     = util::baseName($data['name']);
				$this->fullName = $data['name'];
				$this->ext      = mb_strtolower(util::baseExt($data['name']));
				$this->path     = $data['tmp_name'];
				$this->type     = isset($data['type']) ? $data['type'] : NULL;
				$this->size     = isset($data['size']) ? $data['size'] : NULL;
			}
		}

		/**
		 * @param string $path
		 * @param false  $overwrite
		 * @return TRUE|errorMsg
		 * @throws Exception
		 */
		public function save($path = '', $overwrite = FALSE)
		{
			if ($path and !$this->saved) {
				if (!is_dir(dirname($path))) {
					if (!mkdir($concurrentDirectory = dirname($path), 0777, TRUE) && !is_dir($concurrentDirectory)) {
						throw new Exception(sprintf('Directory "%s" was not created', $concurrentDirectory));
						return 'can`t create directory';
					}
				}
				$converter = [
					'{name}'     => $this->name,
					'{fullName}' => $this->fullName,
					'{ext}'      => $this->ext,
					'{size}'     => $this->size,
					'{type}'     => $this->type,
				];
				$path      = strtr($path, $converter);
				if (!$overwrite and file_exists($path)) {
					throw new Exception('file already exist');
					return 'file already exist';
				}
				if (move_uploaded_file($this->path, $path) and file_exists($path) and filesize($path) > 0) {
					$this->saved = TRUE;
					$this->path  = $path;
					return TRUE;
				} else {
					throw new Exception('can`t save file');
					return 'can`t save file';
				}
			} else {
				throw new Exception('empty path or this already saved');
				return 'empty path or this already saved';
			}
		}

		public function saveTmp()
		: resource
		{
			$f           = tmpfile();
			$metaData    = stream_get_meta_data($f);
			$tmpFilename = $metaData['uri'];
			if (!$this->saved) {
				if (move_uploaded_file($this->path, $tmpFilename) and file_exists($tmpFilename) and filesize($tmpFilename) > 0) {
					return $f;
				} else {
					throw new Exception('can`t save file');
					return 'can`t save file';
				}
			} else {
				if (copy($this->path, $tmpFilename) and file_exists($tmpFilename) and filesize($tmpFilename) > 0) {
					return $f;
				} else {
					throw new Exception('can`t copy file');
					return 'can`t copy file';
				}
			}
		}

		public function __toString()
		{
			return json_encode($this->toArray());
		}

		public function toArray()
		{
			return [
				'name'     => $this->name,
				'fullName' => $this->fullName,
				'path'     => $this->path,
				'ext'      => $this->ext,
				'type'     => $this->type,
				'size'     => $this->size,
			];
		}

		public function __invoke()
		{
			return $this->toArray();
		}

		public function fromJson($flag = JSON_UNESCAPED_UNICODE)
		{
			if ($this->ext == 'json') {
				return json_decode($this->getContent(), $flag);
			}
			return FALSE;
		}

		/**
		 * @return bool|string
		 */
		public function getContent()
		{
			$this->content = @file_get_contents($this->path);
			return $this->content;
		}

		public function __debugInfo()
		{
			return $this->toArray();
		}
	}

	class PostFiles implements \Iterator, \Countable
	{
		public $FILES = [];
		public $containers;
		/**
		 * @var int
		 */
		public $index;
		public $indexes;

		function __construct()
		{
			$this->_FILES  = $_FILES;
			$this->_fields = array_keys($this->_FILES);
			if (is_array($this->_FILES[$this->_fields[0]])) {
				if (array_key_exists('name', $this->_FILES[$this->_fields[0]]) and array_key_exists('tmp_name', $this->_FILES[$this->_fields[0]]) and array_key_exists('type', $this->_FILES[$this->_fields[0]])) {
					$this->_FILES = $this->multiply_files($this->_FILES);
				} else {
					$this->_FILES = $this->default_files($this->_FILES);
				}
			}
			$this->index = 0;
			foreach ($this->_FILES as $input => $file) {
				foreach ($file as $value) {
					$this->indexes[$this->index]    = $input;
					$this->containers[$this->index] = new PostFile($value);
					$this->FILES[$input][]          = $this->containers[$this->index];
					$this->index++;
				}
			}
			$this->index = 0;
		}

		public function multiply_files($files)
		{
			$filesByInput = [];
			foreach ($files as $input => $infoArr) {
				foreach ($infoArr as $key => $valueArr) {
					if (is_array($valueArr)) { // file input "multiple"
						foreach ($valueArr as $i => $value) {
							$filesByInput[$input][$i][$key] = $value;
						}
					} else { // -> string, normal file input
						$filesByInput[$input][0] = $infoArr;
						break;
					}
				}

			}
			return $filesByInput;
		}

		public function default_files($files)
		{
			$filesByInput = [];
			foreach ($files as $input => $value) {
				$filesByInput[$input][0] = $value;
			}
			return $filesByInput;
		}

		public function current()
		{
			return $this->containers[$this->index];
		}

		public function next()
		{
			$this->index++;
		}

		/**
		 * @return [input,id]
		 */
		public function key()
		{
			return ['input' => $this->indexes[$this->index], 'id' => $this->index];
		}

		public function valid()
		{
			return isset($this->containers[$this->index]);
		}

		public function rewind()
		{
			$this->index = 0;
		}

		public function count()
		{
			return count($this->containers);
		}

		public function __invoke()
		{
			return $this->FILES;
		}
	}