<?php

	namespace model\page;

	use Exception;
	use JetBrains\PhpStorm\NoReturn;
	use model\helper\PostFiles;
	use model\main\Core;
	use model\main\CoreObject;
	use model\main\Err;
	use model\main\Utilities;

	/**
	 * Класс для работы ajax запросами
	 */
	abstract class Ajax extends CoreObject
	{
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
		public $ip;
		public $HEADERS;
		/**
		 * @var array|mixed
		 */
		public $data;
		public $FILES;

		public function __construct(Core $core, $data = [])
		{
			parent::__construct($core);
			if (!empty($data)) {
				$this->data = $data;
			}
//			$this->ip               = Utilities::getIp();
//			$this->GET              = $_GET;
			$this->httpResponseCode = 200;
//			$this->POST             = $_POST;
//			$this->PUT              = file_get_contents('php://input');
//			$this->HEADERS          = Utilities::getRequestHeaders();
//			$this->REQUEST          = array_merge($_GET, $_POST);
//			try {
//				if ($put = Utilities::jsonValidate($this->PUT)) {
//					$this->PUT = $put;
//				}
//			} catch (Exception $e) {
//
//			}
//			$this->REQUEST['PUT'] = $this->PUT;
			$this->FILES          = [];
			if (!empty($_FILES)) {
				try {
					$this->FILES = new PostFiles();
				} catch (Exception $e) {
					Err::error($e->getMessage());
				}
			}
		}

		#[NoReturn]
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
			$output = $o;
			if (is_array($o) || is_object($o)) {
				Utilities::headerJson();
				$output = json_encode($o, 256);
			}
			echo $output;
			Utilities::finishRequest();
			$this->afterProcess($o);
		}

		public function initialize()
		{
			return TRUE;
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
			return 'ok';
		}

		public function GET()
		{
			return '';
		}

		public function POST()
		{
			return '';
		}

		public function PUT()
		{
			return '';
		}

		public function DELETE()
		{
			return '';
		}

		public function PATH()
		{
			return '';
		}

		public function CONNECT()
		{
			return '';
		}

		public function HEAD()
		{
			return '';
		}

		public function OPTIONS()
		{
			return '';
		}

		public function TRACE()
		{
			return '';
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

		/**
		 * @param mixed $response
		 * @return void
		 */
		public function afterProcess($response = NULL)
		: void
		{
		}
	}
