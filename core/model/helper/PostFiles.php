<?php

	namespace model\helper;



	use Countable;
	use Exception;
	use Iterator;

	class PostFiles implements Iterator, Countable
	{
		public $FILES = [];
		public $containers;
		/**
		 * @var int
		 */
		public  $index;
		public  $indexes;
		private $_FILES;
		private $_fields;

		/**
		 * @throws Exception
		 */
		function __construct()
		{
			$this->_FILES  = $_FILES;
			$this->_fields = array_keys($this->_FILES);
			if (is_array($this->_FILES[$this->_fields[0]])) {
				if (array_key_exists('name', $this->_FILES[$this->_fields[0]]) && array_key_exists('tmp_name', $this->_FILES[$this->_fields[0]]) && array_key_exists('type', $this->_FILES[$this->_fields[0]])) {
					$this->_FILES = $this->multiplyFiles($this->_FILES);
				} else {
					$this->_FILES = $this->defaultFiles($this->_FILES);
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

		private function multiplyFiles($files)
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

		private function defaultFiles($files)
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
		 * @return array [input,id]
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