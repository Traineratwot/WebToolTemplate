<?php

	namespace core\model;

	use core\classes\users;
	use Exception;
	use Gettext\Generator\ArrayGenerator;
	use Gettext\Generator\JsonGenerator;
	use Gettext\Generator\MoGenerator;
	use Gettext\GettextTranslator;
	use Gettext\Loader\PoLoader;
	use Gettext\Translator;
	use Gettext\TranslatorFunctions;
	use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
	use PDO;
	use PDOException;
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPSQLParser\PHPSQLParser;
	use RecursiveDirectoryIterator;
	use SmartyBC;

	include_once(WT_MODEL_PATH . 'table.php');
	include_once(WT_MODEL_PATH . 'pdo.extended.php');
	include_once(WT_MODEL_PATH . 'postFiles.php');

	/**
	 * Основной класс
	 */
	final class Core implements ErrorPage
	{
		public $db   = NULL;
		public $user = NULL;
		/**
		 * @var SmartyBC
		 */
		public $smarty;
		public $isAuthenticated = FALSE;
		/**
		 * @var Cache
		 */
		public $cache;
		/**
		 * Переводы текущей локали
		 * @var mixed
		 */
		public $translation;

		public function __construct()
		{
			try {
				$this->db = new PDOExtended(WT_DSN_DB, WT_USER_DB, WT_PASS_DB);
			} catch (PDOException $e) {
				Err::fatal($e->getMessage(), __LINE__, __FILE__);
			}
			$this->auth();
			$this->cache = new Cache();
		}

		public function auth()
		{
			if (isset($_SESSION['authKey']) and $_SESSION['authKey'] and $_SESSION['ip'] == util::getIp()) {
				$u = $this->getUser(['authKey' => $_SESSION['authKey']]);
				if (!$u->isNew) {
					$this->user = &$u;
				} else {
					session_unset();
				}
			} else {
				$authKey = strip_tags($_COOKIE['authKey']);
				$id      = (int)$_COOKIE['userId'];
				$u       = $this->getUser($id);
				if (!$u->isNew) {
					if ($authKey == hash('sha256', $u->get('authKey') . util::getIp())) {
						$this->user = &$u;
						$this->user->login();
					}
				}
			}
			if ($this->user == NULL) {
				$this->isAuthenticated = FALSE;
			} else {
				$this->isAuthenticated = TRUE;
			}
		}

		/**
		 * Simple work with csv table
		 * @return table
		 */
		public function newTable()
		: table
		{
			return new table();
		}

		public function getUser($where = [])
		: users
		{
			return new Users($this, $where);
		}

		/**
		 * @param array<string|users>|string|users $to
		 * @param string                           $subject
		 * @param string                           $body
		 * @param array                            $file
		 * @param array                            $options
		 * @return bool|string
		 */
		public function mail($to, $subject, $body, $file = [], $options = [])
		{
			try {
				$mail = new PHPMailer(TRUE);
				$mail->isHTML(TRUE);
				$mail->setLanguage('ru');
				$mail->CharSet = PHPMailer::CHARSET_UTF8;
				if (!empty($options['from'])) {
					if ($options['from'] instanceof Users) {
						$email = $options['from']->get('email');
						$name  = $options['from']->get('full_name');
					} else {
						$a     = explode('::', $options['from']);
						$email = $a[0];
						$name  = $a[1];
					}
					$mail->setFrom($email, $name);
				} else {
					$mail->setFrom(WT_FROM_EMAIL_MAIL, WT_FROM_NAME_MAIL);
				}
				if (WT_SMTP_MAIL) {
					$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
					$mail->isSMTP();                                            //Send using SMTP
					$mail->Host = WT_HOST_MAIL;                                 //Set the SMTP server to send through
					if (WT_AUTH_MAIL) {
						$mail->SMTPAuth = TRUE;                                           //Enable SMTP authentication
						$mail->Username = WT_USERNAME_MAIL;                               //SMTP username
						$mail->Password = WT_PASSWORD_MAIL;                               //SMTP password
					}
					$mail->SMTPSecure = WT_SECURE_MAIL;                                  //Enable implicit TLS encryption
					$mail->Port       = WT_PORT_MAIL;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
				}

				if (!is_array($to)) {
					$to = [$to];
				}
				foreach ($to as $too) {
					if ($too instanceof Users) {
						$email = $too->get('email');
						$name  = $too->get('full_name');
					} else {
						$a     = explode('::', $too);
						$email = $a[0];
						$name  = $a[1];
					}
					$mail->addAddress($email, $name);
				}
				if (!is_array($file)) {
					$file = [$file];
				}
				foreach ($file as $f) {
					$mail->addAttachment($f);
				}
				$mail->Subject = $subject;
				$mail->Body    = $body;
				$mail->AltBody = strip_tags($body);
				$mail->send();
				return TRUE;
			} catch (\PHPMailer\PHPMailer\Exception $e) {
				return $mail->ErrorInfo;
			}
		}

		public static function getClass($class)
		{
			if (class_exists($class)) {
				return $class;
			} else {
				$class = "core\classes\\$class";
				if (class_exists($class)) {
					return $class;
				} else {
					Err::fatal($class . " not exists", __LINE__, __FILE__);
				}
			}
		}

		/**
		 * @param       $class extends bdObject
		 * @param array $where
		 * @return bdObject
		 * @throws Exception
		 */
		public function getObject($class, $where = [])
		{
			$class = Core::getClass($class);
			return new $class($this, $where);
		}

		/**
		 * @param       $class extends bdObject
		 * @param array $where
		 * @return [bdObject]
		 * @throws Exception
		 */
		public function getCollection($class, $where = [], $order_by = NULL, $order_dir = 'ASC')
		{
			$data     = [];
			$class    = Core::getClass($class);
			$cls      = new $class($this);
			$order_by = $order_by ?: $cls->primaryKey;
			if (empty($where)) {
				$sql = "SELECT * FROM `{$cls->table}` ORDER BY `{$order_by}` $order_dir";
			} else {
				$builder = new GenericBuilder();
				$query   = $builder->select()
								   ->setTable($cls->table)
								   ->orderBy($order_by, $order_dir)
								   ->where();
				foreach ($where as $key => $value) {
					$query->equals($key, $value);
				}
				$query  = $query->end();
				$sql    = $builder->write($query);
				$values = $builder->getValues();
				$values = bdObject::prepareBinds($values);
				$sql    = bdObject::prepareSql($sql, $cls->table);
				$sql    = strtr($sql, $values);
			}
			$q = $this->db->query($sql);
			if ($q) {
				while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
					$c = new $class($this);
					/** @var bdObject $c */
					$c->fromArray($row, FALSE);
					$data[] = $c;
				}
			}
			return $data;

		}

		/**
		 * @param $lang //locale code
		 * @param $_gt  //use gettext
		 * @return string|false
		 */
		public function setLocale($_lang, $_gt = TRUE)
		{
			putenv("LANG_FOLDER=$_lang");
			$lang = setlocale(LC_ALL, $_lang);
			if ($lang === FALSE) {
				Err::error("Can't set locale to '{$_lang}'", __LINE__, __FILE__);
				$lang = setlocale(LC_ALL, $_lang, substr($_lang, 0, 2) . '.utf8', substr($_lang, 0, 5) . '.utf8');
			}
			if ($_gt and $lang) {
				$domain = WT_LOCALE_DOMAIN;
				$po     = WT_LOCALE_PATH . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".po";
				if (file_exists($po)) {
					$json = WT_LOCALE_PATH . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".json";
					if (!file_exists($json)) {
						$translations = (new PoLoader())->loadFile($po);
						(new JsonGenerator())->generateFile($translations, $json);
					}
					if (WT_USE_GETTEXT) {
						$mo = WT_LOCALE_PATH . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".mo";
						if (!file_exists($mo)) {
							$translations = (new PoLoader())->loadFile($po);
							(new MoGenerator())->generateFile($translations, $mo);
						}
						$this->translation = json_decode(file_get_contents($json), TRUE);
					} else {
						$php = WT_LOCALE_PATH . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".php";
						if (!file_exists($php)) {
							$translations = (new PoLoader())->loadFile($po);
							(new ArrayGenerator())->generateFile($translations, $php);
						}
						$this->translation = include $php;
					}
				}
				// Задаем текущий язык проекта
				putenv("LANGUAGE=$lang");
				putenv("LANG=$lang");
				putenv("LC_ALL=$lang");
				header('X-locale: ' . $lang);
			} else {
				header('X-locale: ' . $_lang);
			}
			if (WT_USE_GETTEXT) {
				$t = new GettextTranslator();
				if ($lang) {
					$t->setLanguage($lang);
				}
				$t->loadDomain(WT_LOCALE_DOMAIN, WT_LOCALE_PATH);
				bindtextdomain(WT_LOCALE_DOMAIN, WT_LOCALE_PATH);
				textdomain(WT_LOCALE_DOMAIN);
			} else {
				$t = new Translator();
				$t->defaultDomain(WT_LOCALE_DOMAIN);
				if (file_exists($php)) {
					$t->loadTranslations($php);
				}
				TranslatorFunctions::register($t);
			}
			return $lang;
		}

		public static function __set_state($arr)
		{
			global $core;
			return $core;
		}

		public function errorPage($code = 404, $msg = 'Not Found')
		{
			header("HTTP/1.1 $code $msg");
			$errPage = new tmpPage($this, 'errors/' . $code);
			$errPage->setVar('code', $code);
			$errPage->setVar('msg', $msg);
			if ($page = $errPage->render(TRUE)) {
				ob_end_clean();
				exit($page);
			} else {
				ob_end_clean();
				if (file_exists(WT_PAGES_PATH . 'errors/' . $code . '.html')) {
					readfile(WT_PAGES_PATH . 'errors/' . $code . '.html');
				} else {
					readfile(WT_PAGES_PATH . 'errors/' . '404.html');
				}
				exit;
			}
		}
	}

	/**
	 * Класс для работы вместе core
	 */
	abstract class CoreObject
	{
		public $core;

		public function __construct(Core $core)
		{
			$this->core = &$core;
		}
	}

	/**
	 * Класс для работы с таблицей как с объектом
	 *
	 * не забудьте сохранить изменения save()
	 */
	abstract class bdObject extends CoreObject
	{
		public  $table      = '';
		public  $primaryKey = '';
		public  $isNew      = TRUE;
		public  $schema     = [];
		private $update     = [];
		private $data       = [];
		public  $_fields    = [];

		//--------------------------------------------------------
		public function __construct(Core &$core, $where = [])
		{
			parent::__construct($core);
			try {
				$this->getSchema();
			} catch (Exception $e) {
				Err::fatal($e->getMessage(), __LINE__, __FILE__);
			}
			try {
				if (!empty($where)) {
					if (!is_array($where)) {
						if (is_int($where) or is_numeric($where)) {
							$this->update([$this->primaryKey => $where]);
						} else {
							$this->update($where, 1);
						}
					} else {
						$this->update($where);
					}
				}
			} catch (PDOException $e) {
				Err::error($e->getMessage(), __LINE__, __FILE__);
			}
			foreach ($this->_fields as $k => $v) {
				$this->data[$k] = $this->data[$k] ?: NULL;
			}
		}

		private function getSchema($catch = TRUE)
		{
			if ($catch) {
				$data = Cache::getCache($this->table, 'table');
				if (!$data) {
					$data = $this->getColumnNames($this->table);
					Cache::setCache($this->table, $data, 0, 'table');
				}
			} else {
				$data = $this->getColumnNames($this->table);
			}
			if (!empty($data)) {
				foreach ($data as $k => $v) {
					$this->_fields[$v['name']] = $k;
					$this->data[$v['name']]    = $v['default'];
					$this->schema[$v['name']]  = $v;
				}
			}
		}

		private function getColumnNames($table)
		{
			if (WT_TYPE_DB == 'sqlite') {
				$parser = new PHPSQLParser();
				$sql    = "SELECT `name`, `sql` FROM sqlite_master WHERE tbl_name='$table' and type ='table'";
				$stmt   = $this->core->db->query($sql);
				if ($stmt) {
					$output = [];
					$row    = $stmt->fetch(PDO::FETCH_ASSOC);
					$parsed = $parser->parse($row['sql']);
					if (isset($parsed['TABLE'])) {
						foreach ($parsed['TABLE']['create-def']['sub_tree'] as $key => $column) {
							$name = $column['sub_tree'][0]['base_expr'];
							$data = $column['sub_tree'][1];
							foreach ($data['sub_tree'] as $param) {
								if (is_array($param)) {
									if ($param['expr_type'] == 'data-type') {
										$data['data-type'] = $param;
									}
								}
							}
							$default = (!isset($data['default']) or strtolower($data['default']) === 'null') ? NULL : trim($data['default'], '()');
							if (is_numeric($default)) {
								$default = (float)$default;
							}
							$output[$key] = [
								'name'      => $name,
								'default'   => $default,
								'comment'   => '',
								'type'      => $data['data-type']['base_expr'] ?: '',
								'maxLength' => (int)($data['data-type']['length']) ?: NULL,
								'null'      => $data['nullable'] ? TRUE : FALSE,
								'primary'   => (isset($data['unique']) and $data['unique']) ? TRUE : FALSE,
								'unique'    => (isset($data['default']) and $data['default']) ? TRUE : FALSE,
							];
						}
					}
					return $output;
				}
			} else {
				$sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :table";
				try {
					$stmt = $this->core->db->prepare($sql);
					if ($stmt) {
						$stmt->bindValue(':table', $table, PDO::PARAM_STR);
						$stmt->execute();
						$output = [];
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							if ($row['TABLE_SCHEMA'] != 'performance_schema') {
								$output[$row['ORDINAL_POSITION']] = [
									'name'      => $row['COLUMN_NAME'],
									'default'   => $row['COLUMN_DEFAULT'] == 'null' ? NULL : $row['COLUMN_DEFAULT'],
									'comment'   => $row['COLUMN_COMMENT'] ?: '',
									'type'      => $row['DATA_TYPE'] ?: '',
									'maxLength' => (int)$row['COLUMN_MAXIMUM_LENGTH'] ?: NULL,
									'null'      => $row['IS_NULLABLE'] == "YES" ? TRUE : FALSE,
									'primary'   => strtolower($row['COLUMN_KEY']) == "pri" ? TRUE : FALSE,
									'unique'    => strtolower($row['COLUMN_KEY']) == "uni" ? TRUE : FALSE,
								];
							}
						}
						return $output;
					}
				} catch (PDOException $e) {
					Err::error($e->getMessage(), __LINE__, __FILE__);
				}
			}
		}

		private function update($where, $type = 0)
		{
			if (!$type) {
				$builder = new GenericBuilder();
				$query   = $builder->select()
								   ->setTable($this->table)
								   ->where();
				foreach ($where as $key => $value) {
					$query->equals($key, $value);
				}
				$query  = $query->end();
				$sql    = $builder->write($query);
				$values = $builder->getValues();
				$values = $this->prepareBinds($values);
				$sql    = $this->prepareSql($sql, $this->table);
				$sql    = strtr($sql, $values);
			} else {
				$sql = <<<SQL
SELECT * FROM `{$this->table}` WHERE $where
SQL;
			}
			$q = $this->core->db->query($sql);
			if ($q) {
				$data = $q->fetch(PDO::FETCH_ASSOC);
				if ($data and !empty($data)) {
					$this->fromArray($data, FALSE);
					$this->isNew = FALSE;
				}
			} else {
//				Err::warning("invalid sql string: ".$sql);
			}
			return $this;
		}

		public static function prepareBinds($values)
		{
			foreach ($values as $k => $v) {
				if (!is_null($v) and mb_strtolower($v) != 'null' and !empty($v) and !is_numeric($v)) {
					$values[$k] = json_encode($v, 256);
				}
			}
			return $values;
		}

		public static function prepareSql($sql, $table)
		{
			return strtr($sql, [
				$table . '.' => '',
			]);
		}

		public function toArray()
		{
			return $this->data;
		}

		public function fromArray($data, $isNew = TRUE)
		{
			$this->data = array_merge($this->data, $data);
			if (!$this->isNew()) {
				$this->update = array_merge($this->update, $data);
			}
			if ($isNew === FALSE) {
				$this->isNew = FALSE;
			}
			$this->repair();
			return $this;
		}

		//--------------------------------------------------------

		public function set($key, $value = NULL)
		{
			if (is_null($value) and !$this->schema[$key]['null']) {
				$value = $this->schema[$key]['default'];
			}
			if (is_array($value)) {
				$value = json_encode($value, 256);
			}
			if ($this->data[$key] != $value) {
				$this->update[$key] = $value;
			}
			$this->data[$key] = $value;
			$this->repair();
			$this->validate();
			return $this;
		}

		private function repair()
		{
			foreach ($this->data as $key => $value) {
				if (is_numeric($value)) {
					$this->data[$key] = (float)$value;
				}
				if ($this->data[$key] == 'NULL') {
					$this->data[$key] = NULL;
				}
			}
			foreach ($this->update as $key => $value) {
				if (is_numeric($value)) {
					$this->update[$key] = (float)$value;
				}
				if (stripos($value, 'NULL') === 0 and strlen($value) == 4) {
					$this->update[$key] = NULL;
				}
			}
		}

		private function validate()
		{
			foreach ($this->data as $key => $value) {
				if (!array_key_exists($key, $this->_fields)) {
					Err::warning('undefined field: "' . $key . '" in "' . $this->table . '"', __FILE__, __FILE__);
				}
			}
			foreach ($this->update as $key => $value) {
				if (!array_key_exists($key, $this->_fields)) {
					Err::warning('undefined field: "' . $key . '" in "' . $this->table . '"', __FILE__, __FILE__);
				}
			}
			return $this;
		}

		public function save()
		{
			try {
				$sql = NULL;
				$this->validate();
				$builder = new GenericBuilder();
				if ($this->isNew()) {
					$query = $builder->insert()
									 ->setTable($this->table)
									 ->setValues($this->update);

					$sql    = $builder->write($query);
					$values = $builder->getValues();
				} elseif (count($this->update) > 0) {
					$query = $builder->update()
									 ->setTable($this->table)
									 ->setValues($this->update)
									 ->where()
									 ->equals($this->primaryKey, $this->data[$this->primaryKey])
									 ->end();

					$sql    = $builder->write($query);
					$values = $builder->getValues();
				}
				if ($sql) {
					$values = $this->prepareBinds($values);
					$sql    = $this->prepareSql($sql, $this->table);
					$sql    = strtr($sql, $values);
					//Err::info($sql, __LINE__, __FILE__);
					$q = $this->core->db->exec($sql);
					if ($q !== FALSE and $this->isNew()) {
						$lastID = $this->core->db->lastInsertId();
						if ($lastID) {
							$this->data[$this->primaryKey] = $lastID;
						}
						$this->isNew = FALSE;
					} else {
						Err::error($sql, __LINE__, __FILE__);
					}
				}
				$this->update([$this->primaryKey => $this->data[$this->primaryKey]]);
			} catch (PDOException $e) {
				Err::error($e->getMessage(), __LINE__, __FILE__);
			}

			return $this;
		}

		public function isNew()
		{
			return $this->isNew;
		}

		public function remove()
		{
			try {
				if (!$this->isNew()) {
					$id  = $this->get($this->primaryKey);
					$sql = <<<SQL
DELETE FROM `{$this->table}` where `{$this->primaryKey}` = {$id}
SQL;
					$q   = $this->core->db->exec($sql);
				}
			} catch (PDOException $e) {
				Err::error($e->getMessage(), __LINE__, __FILE__);
			}
			return $this;
		}

		public function get($key, $default = NULL)
		{
			$this->repair();
			if (is_null($default) and isset($this->schema[$key])) {
				$default = $this->schema[$key]['null'] ? NULL : $this->schema[$key]['default'];
			}
			return $this->data[$key] ?: $default;
		}

		/** @noinspection MagicMethodsValidityInspection */
		public static function __set_state(array $array)
		{
			global $core;
			$a   = get_called_class();
			$obj = new $a($core);
			$obj->fromArray($array['data'], FALSE);
			return $obj;
		}
	}

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
		public $LanguageTopics = [];
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

		public function __construct(Core &$core, $data = [])
		{
			parent::__construct($core);
			if (!empty($data)) {
				$this->data = $data;
			}
			$this->GET              = $_GET;
			$this->httpResponseCode = 200;
			$this->POST             = $_POST;
			$this->PUT              = file_get_contents('php://input');
			$this->HEADERS          = util::getRequestHeaders();
			$this->REQUEST          = array_merge($_GET, $_POST);
			try {
				if ($put = util::jsonValidate($this->PUT)) {
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
			foreach ($this->LanguageTopics as $topic) {
				$this->core->lexicon->load($topic);
			}

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
				util::headerJson();
				$o = json_encode($o, 256);
			}
			return (string)$o;
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
	}

	/**
	 * Класс для Страницы
	 */
	abstract class Page extends CoreObject implements ErrorPage
	{

		public $alias;
		public $title;
		public $data = NULL;

		public function __construct(Core $core, $data = [])
		{
			parent::__construct($core);
			if (!empty($data)) {
				$this->data = $data;
			}
			if (!$this->alias) {
				$this->alias = $_GET['q'];
			}
			$this->source = WT_PAGES_PATH . $this->alias . '.tpl';
			if (!$this->title) {
				$this->title = util::basename($this->alias) ?: $this->alias;
			}
			$this->smarty = new SmartyBC();
			$this->init();
		}

		public function init()
		{
			$this->smarty->addPluginsDir(WT_SMARTY_PLUGINS_PATH . '/');
			$this->smarty->setTemplateDir(WT_SMARTY_TEMPLATE_PATH . '/');
			$this->smarty->setCompileDir(WT_SMARTY_COMPILE_PATH . '/');
			$this->smarty->setConfigDir(WT_SMARTY_CONFIG_PATH . '/');
			$this->smarty->setCacheDir(WT_SMARTY_CACHE_PATH . '/');
			$this->smarty->assignGlobal('page', $this);
			$this->smarty->assignGlobal('core', $this->core);
			$this->smarty->assignGlobal('user', $this->core->user);
			$this->smarty->assignGlobal('_GET', $_GET);
			$this->smarty->assignGlobal('_POST', $_POST);
			$this->smarty->assignGlobal('_COOKIE', $_COOKIE);
			$this->smarty->assignGlobal('_REQUEST', $_REQUEST);
			$this->smarty->assignGlobal('_SERVER', $_SERVER);
			$this->smarty->assignGlobal('isAuthenticated', $this->core->isAuthenticated);
			$this->addModifier('user', '\core\model\Page::modifier_user');
			$this->addModifier('chunk', '\core\model\Page::chunk');
		}

		public function addModifier($name, $function)
		{
			$this->smarty->registerPlugin("modifier", $name, $function);
		}

		/**
		 * @return mixed
		 */
		public static function modifier_user($value)
		{
			global $core;
			$value = (int)$value;
			if ($value) {
				return $core->getUser($value);
			}
			return $value;
		}

		public function render($return = FALSE)
		{
			if ($return) {
				ob_end_flush();
				ob_start();
			}
			$this->beforeRender();
			$this->smarty->assignGlobal('title', $this->title);
			if (!file_exists($this->source)) {
				Err::fatal('can`t load: ' . $this->source);
				return FALSE;
			}
			$this->smarty->display($this->source);
			if ($return) {
				return ob_get_clean();
			}
			return TRUE;
		}

		public function beforeRender()
		{

		}

		public function forward($alias)
		{
			if (file_exists(WT_PAGES_PATH . $alias . '.tpl')) {
				$this->alias  = $alias;
				$this->source = WT_PAGES_PATH . $alias . '.tpl';
				return TRUE;
			} else {
				return FALSE;
			}
		}

		public static function redirect($alias)
		{
			header("Location: $alias");
		}

		public function setVar($name, $var, $nocache = FALSE)
		{
			$this->smarty->assign($name, $var, $nocache);
		}

		public static function chunk($alias, $values = [])
		{
			global $core;
			$a = new Chunk($core, $alias, $values);
			return $a->render(TRUE);
		}

		public function errorPage($code = 404, $msg = 'Not Found')
		{
			$this->core->errorPage($code, $msg);
		}
	}

	/**
	 * Класс для Чанка
	 */
	class Chunk extends Page
	{
		public function __construct(Core $core, $alias, $values)
		{
			parent::__construct($core);
			$this->source = WT_TEMPLATES_PATH . $alias . '.tpl';
			foreach ($values as $key => $value) {
				$this->setVar($key, $value);
			}
		}

		public function render($return = TRUE)
		{
			return parent::render($return);
		}
	}

	/**
	 * Класс для Кеша
	 *
	 * [gist.github.com](https://gist.github.com/Traineratwot/4cc7caa49f5b8951e434b241b84063dd)
	 */
	class Cache
	{
		/**
		 * @param $key      mixed
		 * @param $value    mixed
		 * @param $expire   int
		 * @param $category string
		 * @return mixed
		 */
		public static function setCache($key, $value, $expire = 600, $category = '')
		{
			$name                = Cache::getKey($key) . '.cache.php';
			$v                   = var_export($value, 1);
			$expire              = $expire ? $expire + time() : 0;
			$body                = <<<PHP
<?php
	if($expire){if(time()>$expire){unlink(__FILE__);return null;}}
	return $v
?>
PHP;
			$concurrentDirectory = WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR;
			if (!file_exists($concurrentDirectory) or !is_dir($concurrentDirectory)) {
				if (!mkdir($concurrentDirectory, 0777, TRUE) && !is_dir($concurrentDirectory)) {
					throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
				}
			}
			if (is_dir($concurrentDirectory)) {
				file_put_contents($concurrentDirectory . $name, $body);
			}
			return $value;
		}

		/**
		 * @param $a mixed
		 * @return string
		 */
		public static function getKey($a)
		{
			if (is_string($a) and strlen($a) < 32 and preg_match('@\w{1,32}@', $a)) {
				return $a;
			}
			return md5(serialize($a));
		}

		/**
		 * @param $key      mixed
		 * @param $category string
		 * @return mixed|null
		 */
		public static function getCache($key, $category = '')
		{
			if ($category != 'table') {
				if (function_exists('getallheaders')) {
					$headers = getallheaders();
					if ($headers['Cache-Control'] == 'no-cache') {
						return NULL;
					}
				}
			}
			$name = Cache::getKey($key) . '.cache.php';
			if (file_exists(WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name)) {
				return include WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name;
			}
			return NULL;
		}

		/**
		 * @param $key      mixed
		 * @param $category string
		 * @return bool
		 */
		public static function removeCache($key, $category = '')
		{
			$name = Cache::getKey($key) . '.cache.php';
			if (file_exists(WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name)) {
				unlink(WT_CACHE_PATH . $category . DIRECTORY_SEPARATOR . $name);
			}
			return !file_exists(WT_CACHE_PATH . $name);
		}

		public static function __set_state($arr)
		{
			return new Cache();
		}
	}

	/**
	 * Класс дла консоли
	 *
	 * [gist.github.com](https://gist.github.com/Traineratwot/fda0574a0427104e8dd30fed870810ec)
	 */
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

	/**
	 * Класс с утилитами
	 */
	class util
	{
		public static function jsonValidate($string, $assoc = TRUE, $depth = 1024)
		{
			if (!is_string($string)) {
				return $string;
			}
			try {
				$error = 0;
				// decode the JSON data
				$string = preg_replace('/[[:cntrl:]]/', '', $string);
				if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
					$result = json_decode($string, (bool)$assoc, $depth, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
				} else {
					$result = json_decode($string, (bool)$assoc, $depth, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
				}

				// switch and check possible JSON errors
				switch (json_last_error()) {
					case JSON_ERROR_NONE:
						$error = 0; // JSON is valid // No error has occurred
						break;
					case JSON_ERROR_DEPTH:
						$error = 'The maximum stack depth has been exceeded.';
						break;
					case JSON_ERROR_STATE_MISMATCH:
						$error = 'Invalid or malformed JSON.';
						break;
					case JSON_ERROR_CTRL_CHAR:
						$error = 'Control character error, possibly incorrectly encoded.';
						break;
					case JSON_ERROR_SYNTAX:
						$error = 'Syntax error, malformed JSON.';
						break;
					// PHP >= 5.3.3
					case JSON_ERROR_UTF8:
						$error = 'Malformed utf8 characters, possibly incorrectly encoded.';
						break;
					// PHP >= 5.5.0
					case JSON_ERROR_RECURSION:
						$error = 'One or more recursive references in the value to be encoded.';
						break;
					// PHP >= 5.5.0
					case JSON_ERROR_INF_OR_NAN:
						$error = 'One or more NAN or INF values in the value to be encoded.';
						break;
					case JSON_ERROR_UNSUPPORTED_TYPE:
						$error = 'A value of a type that cannot be encoded was given.';
						break;
					default:
						$error = 'Unknown JSON error occurred.';
						break;
				}
				if (!$error) {
					return $result;
				}
				return FALSE;
			} catch (Exception $e) {
				Err::fatal($e->getMessage(), __LINE__, __FILE__);
				return FALSE;
			}

		}

		public static function ping($host = '', $useSocket = FALSE, $timeout = 2, $port = 80)
		{
			$_args = func_get_args();
			if (count($_args) == 1 and is_array($_args[0])) {
				extract($_args[0], EXTR_OVERWRITE);
			}
			if ($host) {
				$sock = FALSE;
				if ($useSocket) {
					$sock = @fsockopen($host, $port, $errno, $errStr, $timeout);
				}
				if (!$sock) {
					if (!$useSocket or $errStr == 'Unable to find the socket transport "https" - did you forget to enable it when you configured PHP?') {
						$opts = [
							'http'  => [
								'timeout' => $timeout,
								'header'  => "User - Agent: Mozilla / 5.0\r\n",

							],
							'https' => [
								'timeout' => $timeout,
								'header'  => "User - Agent: Mozilla / 5.0\r\n",
							],
						];
						if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
							$context = stream_context_create($opts);
							$headers = @get_headers($host, 1, $context); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
						} else {
							stream_context_set_default($opts);
							$headers = @get_headers($host, 1); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
						}
						preg_match('@HTTP\/\d+.\d+\s([2-3]\d+)?\s@', $headers[0], $math);
						if (isset($math[1]) and $math[1]) {
							return TRUE;
						} else {
							return FALSE;
						}
					}
				} else {
					return TRUE;
				}
			}
			return FALSE;
		}

		public static function success($msg, $object = [])
		{
			return json_encode([
								   'success' => TRUE,
								   'message' => $msg,
								   'object'  => $object,
							   ], 256);

		}

		public static function failure($msg, $object = [])
		{
			return json_encode([
								   'success' => FALSE,
								   'message' => $msg,
								   'object'  => $object,
							   ], 256);

		}

		public static function setCookie($name, $value, $time = 0)
		{
			$expire = $time ?: time() + 31556926;
			setcookie($name, $value, $expire, '/');
		}

		public static function id($length = 6)
		{
			$length--;
			$password = 'a';
			$arr      = [
				'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
				'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
				'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
				'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
				'1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
			];
			for ($i = 0; $i < $length; $i++) {
				$password .= $arr[random_int(0, count($arr) - 1)];
			}
			return $password;
		}

		public static function getIp()
		{
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			return filter_var($ip, FILTER_VALIDATE_IP) ? (string)$ip : FALSE;
		}

		public static function getSystem()
		{
			$sys = util::rawText(php_uname('s'));
			if (strpos($sys, 'windows') !== FALSE) {
				return 'win';
			}
			if (strpos($sys, 'linux') !== FALSE) {
				return 'nix';
			}
			return 'nix';
		}

		public static function rawText($a = '')
		{
			return mb_strtolower(preg_replace('@[^A-zА-я0-9]|[\/_\\\.\,]@u', '', (string)$a));
		}

		public static function baseExt($file = '')
		{
			$_tmp = explode('.', basename($file));
			return end($_tmp);
		}

		/**
		 * @param string $file
		 * @return string
		 */
		public static function baseName($file = '')
		{
			$_tmp = explode('.', basename($file));
			array_pop($_tmp);
			return implode('', $_tmp);
		}

		public static function getRequestHeaders()
		{
			if (function_exists('getallheaders')) {
				return getallheaders();
			} else {
				if (!is_array($_SERVER)) {
					return [];
				}
				$headers = [];
				foreach ($_SERVER as $name => $value) {
					if (substr($name, 0, 5) == 'HTTP_') {
						$key           = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
						$headers[$key] = $value;
					}
				}
				return $headers;
			}
			return [];
		}

		public static function headerJson()
		{
			@header("Content-type: application/json; charset=utf8");
		}

		public static function getSetOption($table = '', $column = '')
		{
			if (empty($table) or empty($column)) {
				return FALSE;
			}
			global $core;
			if (!($ret = $core->db->query("SHOW COLUMNS FROM `$table` LIKE '$column'"))) {
				return FALSE;
			}
			$line = $ret->fetch(PDO::FETCH_ASSOC);
			$set  = rtrim(ltrim(preg_replace('@^[setnum]+@', '', $line['Type']), "('"), "')");
			return preg_split("/','/", $set);
		}

		/**
		 * Recursive `glob()`.
		 * @param string $baseDir Base directory to search
		 * @param string $pattern Glob pattern
		 * @param int    $flags   Behavior bitmask
		 * @return array|string|bool
		 */
		public static function glob(string $baseDir, string $pattern, int $flags = GLOB_NOSORT | GLOB_BRACE)
		{
			$dirs       = new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS);
			$fileList   = [];
			$fileList[] = glob($baseDir . DIRECTORY_SEPARATOR . $pattern, $flags);
			foreach ($dirs as $dir) {
				$fileList[] = glob(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pattern, $flags);
			}
			$fileList = array_unique(array_merge(...$fileList));
			foreach ($fileList as $k => $file) {
				$fileList[$k] = realpath($file);
			}
			return $fileList;
		}
	}

	class make
	{
		public static function makeAjax($name, $type = 'any')
		{
			$method = '';
			switch ($type) {
				case 'post':
					$method = <<<PHP
	function POST(){
		//TODO YOU CODE
	}
PHP;
					break;
				case 'get':
					$method = <<<PHP
	function GET(){
		//TODO YOU CODE
	}
PHP;
					break;
				default:
					$method = <<<PHP
	function process(){
		//TODO YOU CODE
	}
PHP;
					break;
			}
			$class = make::name2class($name);
			$code  = <<<PHP
<?php
	namespace core\ajax;
	use core\model\Ajax;
	class {$class} extends Ajax
	{
		{$method}
	}
	return '{$class}';
PHP;
			return $code;
		}

		public static function name2class($name)
		{
			$name = strtr($name, [
				'\\' => '_',
				'/'  => '_',
				'-'  => '_',
				' '  => '_',
				'*'  => '_',
				'.'  => '_',
				'+'  => '_',
			]);
			$n    = explode("_", $name);
			$n2   = [];
			foreach ($n as $key => $value) {
				$n2[] = ucfirst(mb_strtolower($value));
			}
			$class = implode('', $n2);
			return $class;
		}

		public static function makePageTpl($name, $template = 'base')
		{
			if (!$template) {
				$template = 'base.tpl';
			} else {
				$template = $template . '.tpl';
			}
			$code = <<<TPL
{extends file='{$template}'}
{block name="head"}

{/block}
{block name='content'}
	
{/block}
TPL;
			return $code;
		}

		public static function makePageClass($name, $template = 'base')
		{
			$class = make::name2class($name);
			$code  = <<<PHP
<?php

	namespace core\page;

	use core\model\Err;
	use core\model\Page;

	class {$class} extends Page
	{
		public \$alias = '$name';
		public \$title = '$name';

		public function beforeRender(){

		}
	}

	return '{$class}';
PHP;
			return $code;
		}

		public static function makeTemplate($name, $template = 'base')
		{
		}

		public static function makeTable($name, $primaryKey = 'id')
		{
			$primaryKey = $primaryKey ?: 'id';
			$class      = make::name2class($name);
			$code       = <<<PHP
<?php

	namespace core\\classes;
	use core\\model\\bdObject;

	/**
	 * Класс для работы с таблицей `$name`
	 * вызывается core::getObject('$class')
	 */
	class $class extends bdObject
	{
		public \$table = '$name';
		public \$primaryKey = '$primaryKey';
	}
PHP;
			return $code;
		}
	}

	class tmpPage extends Page
	{
		public function __construct(Core $core, $alias, $data = [])
		{
			$this->alias = $alias;
			parent::__construct($core, $data);
		}
	}

	interface ErrorPage
	{
		public function errorPage($code, $msg);
	}