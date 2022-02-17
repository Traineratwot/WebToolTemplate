<?php

	namespace model\page;

	use Exception;
	use model\main\Core;
	use model\main\CoreObject;
	use traits\Utilities;
	use traits\validators\jsonValidate;

	/**
	 * Класс для работы ajax запросами
	 */
	abstract class Ajax extends CoreObject
	{
		use Utilities;
		use jsonValidate;

		/**
		 * @var array
		 */
		public $headers = [];
		/**
		 * @var array
		 */
		public $GET;
		/**
		 * @var array
		 */
		public $POST;
		/**
		 * @var array
		 */
		public $PUT;
		/**
		 * @var array
		 */
		public $REQUEST;
		/**
		 * query
		 * @var string
		 */
		public $url;
		/**
		 * query
		 * @var integer
		 */
		public $httpResponseCode = 0;

		public function __construct(Core $core, $data = [])
		{
			parent::__construct($core);
			if (!empty($data)) {
				$this->data = $data;
			}
			$this->GET              = $_GET;
			$this->httpResponseCode = 200;
			$this->POST             = $_POST;
			$this->PUT              = file_get_contents('php://input');
			$this->HEADERS          = self::getRequestHeaders();
			$this->REQUEST          = array_merge($_GET, $_POST);
			try {
				if ($put = self::jsonValidate($this->PUT)) {
					$this->PUT = $put;
				}
			} catch (Exception $e) {

			}
			$this->REQUEST['PUT'] = $this->PUT;
			$this->FILES          = [];
			if (!empty($_FILES)) {
				try {
					$this->FILES = new PostFiles();
				} catch (Exception $e) {
					Err::error($e->getMessage(), __LINE__, __FILE__);
				}
			}
		}

		final public function run()
		{
			$initialized = $this->initialize();
			if ($initialized !== TRUE) {
				$o = $this->failure($initialized);
			} else {
				$o = $this->process();
				if ($this->httpResponseCode) {
					http_response_code($this->httpResponseCode);
				}
			}
			foreach ($this->headers as $key => $value) {
				header("$key: $value");
			}
			if (is_array($o) or is_object($o)) {
				self::headerJson();
				$o = json_encode($o, 256);
			}
			return (string)$o;
		}

		public function initialize()
		{
			return TRUE;
		}



		public function process()
		{
			switch ($_SERVER['REQUEST_METHOD']) {
				case 'GET':
					return $this->GET();
				case 'POST':
					return $this->POST();
				case 'PUT':
					return $this->PUT();
				case 'DELETE':
					return $this->DELETE();
				case 'PATH':
					return $this->PATH();
				case 'CONNECT':
					return $this->CONNECT();
				case 'HEAD':
					return $this->HEAD();
				case 'OPTIONS':
					return $this->OPTIONS();
				case 'TRACE':
					return $this->TRACE();
			}
		}

		public function GET()
		{
		}

		public function POST()
		{
		}

		public function PUT()
		{
		}

		public function DELETE()
		{
		}

		public function PATH()
		{
		}

		public function CONNECT()
		{
		}

		public function HEAD()
		{
		}

		public function OPTIONS()
		{
		}

		public function TRACE()
		{
		}

		public function success($msg = '', $object = NULL)
		{
			return [
				'success' => TRUE,
				'message' => $msg,
				'object'  => $object,
				'code'    => $this->httpResponseCode,
			];
		}
		public function failure($msg = '', $object = NULL, $error = [])
		{
			return [
				'success' => FALSE,
				'message' => $msg,
				'object'  => $object,
				'errors'  => $error,
				'code'    => $this->httpResponseCode,
			];
		}
	}