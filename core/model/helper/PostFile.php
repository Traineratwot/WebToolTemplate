<?php

	namespace model\helper;


	use Exception;
	use model\main\Utilities;

	class PostFile
	{
		public $name;
		public $path;
		public $ext;
		public $fullName;
		public $type;
		/**
		 * @var int
		 */
		public $size;
		public $content;
		/**
		 * @var bool
		 */
		private $saved = FALSE;
		/**
		 * @var mixed
		 */
		private $error;
		private $data;


		/**
		 * PostFile constructor.
		 * @param array $data
		 * @throws Exception
		 */
		public function __construct($data)
		{
			$this->data = $data;
			if (!isset($data['name']) || !isset($data['tmp_name'])) {
				throw new Exception('name || path not found');
			}

			if ($data['error'] != UPLOAD_ERR_OK) {
				$this->error = $data['error'];
				throw new Exception('upload error: "' . $data['error'] . '"');
			}

			$this->name     = Utilities::baseName($data['name']);
			$this->fullName = $data['name'];
			$this->ext      = mb_strtolower(Utilities::baseExt($data['name']));
			$this->path     = $data['tmp_name'];
			$this->type     = $data['type'] ?? NULL;
			$this->size     = $data['size'] ?? NULL;
		}

		/**
		 * @param string $path
		 * @param false  $overwrite
		 * @return bool 'errorMsg'
		 * @throws Exception
		 */
		public function save($path = '', $overwrite = FALSE)
		{
			$converter = [
				'{name}'     => $this->name,
				'{fullName}' => $this->fullName,
				'{ext}'      => $this->ext,
				'{size}'     => $this->size,
				'{type}'     => $this->type,
			];
			$path      = strtr($path, $converter);
			if ($path && !$this->saved) {
				if (!is_dir(dirname($path)) && !mkdir($concurrentDirectory = dirname($path), 0777, TRUE) && !is_dir($concurrentDirectory)) {
					throw new Exception(sprintf('Directory "%s" was not created', $concurrentDirectory));
					return 'can`t create directory';
				}
				if (!$overwrite && file_exists($path)) {
					throw new Exception('file already exist');
					return 'file already exist';
				}
				if (move_uploaded_file($this->path, $path) && file_exists($path) && filesize($path) > 0) {
					$this->saved = TRUE;
					$this->path  = $path;
					return TRUE;
				}

				throw new Exception('can`t save file');
				return 'can`t save file';
			}

			throw new Exception('empty path || this already saved');
			return 'empty path || this already saved';
		}

		/**
		 * @return bool|resource
		 * @throws Exception
		 */
		public function saveTmp()
		{
			$f           = tmpfile();
			$metaData    = stream_get_meta_data($f);
			$tmpFilename = $metaData['uri'];
			if (!$this->saved) {
				if (move_uploaded_file($this->path, $tmpFilename) && file_exists($tmpFilename) && filesize($tmpFilename) > 0) {
					return $f;
				}

				throw new Exception('can`t save file');
				return 'can`t save file';
			}

			if (copy($this->path, $tmpFilename) && file_exists($tmpFilename) && filesize($tmpFilename) > 0) {
				return $f;
			}

			throw new Exception('can`t copy file');
			return 'can`t copy file';
		}

		public function __toString()
		{
			return (string)json_encode($this->toArray());
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
			if (!$this->content) {
				$this->content = @file_get_contents($this->path);
			}
			return $this->content;
		}

		public function __debugInfo()
		{
			return $this->toArray();
		}
	}