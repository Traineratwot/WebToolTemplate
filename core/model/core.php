<?php

	namespace core\model;

	use core\classes\users;
	use Exception;
	use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
	use PDO;
	use PDOException;
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPSQLParser\PHPSQLParser;
	use SmartyBC;

	/**
	 * Основной класс
	 */
	final class Core
	{
		public $db = NULL;
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

		public function __construct()
		{
			try {
				$this->db = new PDO(WT_DSN_DB, WT_USER_DB, WT_PASS_DB);
			} catch (PDOException $e) {
				Err::fatal($e->getMessage(), __LINE__, __FILE__);
			}
			$this->auth();
			$this->cache = new Cache;
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
			}
			if ($this->user == NULL) {
				$this->isAuthenticated = FALSE;
			} else {
				$this->isAuthenticated = TRUE;
			}
		}

		public function getUser($where = []): users
		{
			return new Users($this, $where);
		}

		/**
		 * @param array<string|users>|string|users $to
		 * @param string                           $subject
		 * @param string                           $body
		 * @param array                            $file
		 * @return bool|string
		 */
		public function mail($to, $subject, $body, $file = [])
		{
			try {
				$mail = new PHPMailer(TRUE);
				$mail->isHTML(TRUE);
				$mail->setLanguage('ru');
				$mail->CharSet = PHPMailer::CHARSET_UTF8;
				$mail->setFrom(WT_FROM_EMAIL_MAIL, WT_FROM_NAME_MAIL);
				if (WT_SMTP_MAIL) {
					$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
					$mail->isSMTP();                                            //Send using SMTP
					$mail->Host = WT_HOST_MAIL;                     //Set the SMTP server to send through
					if (WT_AUTH_MAIL) {
						$mail->SMTPAuth = TRUE;                                   //Enable SMTP authentication
						$mail->Username = WT_USERNAME_MAIL;                     //SMTP username
						$mail->Password = WT_PASSWORD_MAIL;                               //SMTP password
					}
					$mail->SMTPSecure = WT_SECURE_MAIL;            //Enable implicit TLS encryption
					$mail->Port = WT_PORT_MAIL;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
				}

				if (!is_array($to)) {
					$to = [$to];
				}
				foreach ($to as $too) {
					if ($too instanceof Users) {
						$email = $too->get('email');
						$name = $too->get('full_name');
					} else {
						$a = explode('::', $too);
						$email = $a[0];
						$name = $a[1];
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
				$mail->Body = $body;
				$mail->AltBody = strip_tags($body);
				$mail->send();
				return TRUE;
			} catch (\PHPMailer\PHPMailer\Exception $e) {
				return $mail->ErrorInfo;
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
			if (class_exists($class)) {
				return new $class($this, $where);
			} else {
				$class = "core\classes\\$class";
				if (class_exists($class)) {
					return new $class($this, $where);
				} else {
					Err::fatal($class . " not exists", __FILE__, __FILE__);
				}
			}
		}

		/**
		 * @param       $class extends bdObject
		 * @param array $where
		 * @return [bdObject]
		 * @throws Exception
		 */
		public function getCollection($class, $where = [])
		{
			$data = [];
			$class = "core\classes\\$class";
			if (class_exists($class)) {
				$cls = new $class($this);
				if (empty($where)) {
					$sql = "SELECT `{$cls->primaryKey}` FROM `{$cls->table}`";
				} else {
					$builder = new GenericBuilder();
					$query = $builder->select()
						->setTable($cls->table)
						->where();
					foreach ($where as $key => $value) {
						$query->equals($key, $value);
					}
					$query = $query->end();
					$sql = $builder->write($query);
					$values = $builder->getValues();
					$values = bdObject::prepareBinds($values);
					$sql = bdObject::prepareSql($sql, $cls->table);
					$sql = strtr($sql, $values);
				}
				$q = $this->db->query($sql);
				if ($q) {
					while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
						$id = $row[$cls->primaryKey];
						$key = $cls->primaryKey;
						$data[] = new $class($this, [$key => $id]);
					}
				}
				return $data;
			} else {
				Err::fatal($class . " not exists", __FILE__, __FILE__);
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
		public $table = '';
		public $primaryKey = '';
		public $isNew = TRUE;
		public $schema = [];
		public $update = [];
		public $data = [];
		public $_fields = [];

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
				$c = WT_CACHE_PATH . $this->table . '.json';
				if (file_exists($c)) {
					$data = json_decode(file_get_contents($c), 1);
				} else {
					$data = $this->getColumnNames($this->table);
					file_put_contents($c, json_encode($data, 256));
				}
			} else {
				$data = $this->getColumnNames($this->table);
			}
			if (!empty($data)) {
				foreach ($data as $k => $v) {
					$this->_fields[$v['name']] = $k;
					$this->data[$v['name']] = $v['default'];
					$this->schema[$v['name']] = $v;
				}
			}
		}

		private function getColumnNames($table)
		{
			if (WT_TYPE_DB == 'sqlite') {
				$parser = new PHPSQLParser();
				$sql = "SELECT `name`, `sql` FROM sqlite_master WHERE tbl_name='$table' and type ='table'";
				$stmt = $this->core->db->query($sql);
				if ($stmt) {
					$output = [];
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
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
								'name' => $name,
								'default' => $default,
								'comment' => '',
								'type' => $data['data-type']['base_expr'] ?: '',
								'maxLength' => (int)($data['data-type']['length']) ?: NULL,
								'null' => $data['nullable'] ? TRUE : FALSE,
								'primary' => (isset($data['unique']) and $data['unique']) ? TRUE : FALSE,
								'unique' => (isset($data['default']) and $data['default']) ? TRUE : FALSE,
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
									'name' => $row['COLUMN_NAME'],
									'default' => $row['COLUMN_DEFAULT'] == 'null' ? NULL : $row['COLUMN_DEFAULT'],
									'comment' => $row['COLUMN_COMMENT'] ?: '',
									'type' => $row['DATA_TYPE'] ?: '',
									'maxLength' => (int)$row['COLUMN_MAXIMUM_LENGTH'] ?: NULL,
									'null' => $row['IS_NULLABLE'] == "YES" ? TRUE : FALSE,
									'primary' => strtolower($row['COLUMN_KEY']) == "pri" ? TRUE : FALSE,
									'unique' => strtolower($row['COLUMN_KEY']) == "uni" ? TRUE : FALSE,
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
				$query = $builder->select()
					->setTable($this->table)
					->where();
				foreach ($where as $key => $value) {
					$query->equals($key, $value);
				}
				$query = $query->end();
				$sql = $builder->write($query);
				$values = $builder->getValues();
				$values = $this->prepareBinds($values);
				$sql = $this->prepareSql($sql, $this->table);
				$sql = strtr($sql, $values);
			} else {
				$sql = <<<SQL
SELECT * FROM `{$this->table}` WHERE $where
SQL;
			}
			$q = $this->core->db->query($sql);
			if ($q) {
				$data = $q->fetch(PDO::FETCH_ASSOC);
				if ($data and !empty($data)) {
					foreach ($data as $k => $v) {
						$this->data[$k] = $v;
					}
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
			if (WT_TYPE_DB != 'sqlite') {
				return $sql;
			}
			return strtr($sql, [
				$table . '.' => '',
			]);
		}

		public function toArray()
		{
			return $this->data;
		}

		public function fromArray($data)
		{
			array_merge($this->data, $data);
			array_merge($this->update, $data);
			return $this;
		}

		//--------------------------------------------------------

		public function set($key, $value = NULL)
		{
			if (is_null($value) and !$this->schema[$key]['null']) {
				$value = $this->schema[$key]['default'];
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
				if (stripos($value, 'NULL') === 0 and strlen($value) == 4) {
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
						->setValues($this->data);

					$sql = $builder->write($query);
					$values = $builder->getValues();
				} elseif (count($this->update) > 0) {
					$query = $builder->update()
						->setTable($this->table)
						->setValues($this->update)
						->where()
						->equals($this->primaryKey, $this->data[$this->primaryKey])
						->end();

					$sql = $builder->write($query);
					$values = $builder->getValues();
				}
				if ($sql) {
					$values = $this->prepareBinds($values);
					$sql = $this->prepareSql($sql, $this->table);
					$sql = strtr($sql, $values);
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
					$id = $this->get($this->primaryKey);
					$sql = <<<SQL
DELETE FROM `{$this->table}` where `{$this->primaryKey}` = {$id}
SQL;
					$q = $this->core->db->exec($sql);
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
				$default = $this->schema[$key]['default'];
			}
			return $this->data[$key] ?: $default;
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

		public function __construct(Core &$core)
		{
			parent::__construct($core);
			$this->GET = $_GET;
			$this->httpResponseCode = 200;
			$this->POST = $_POST;
			$this->PUT = file_get_contents('php://input');
			$this->HEADERS = util::getRequestHeaders();
			$this->REQUEST = array_merge($_GET, $_POST);
			try {
				if ($put = util::jsonValidate($this->PUT)) {
					$this->PUT = $put;
				}
			} catch (Exception $e) {

			}
			$this->REQUEST['PUT'] = $this->PUT;
			$this->FILES = [];
			if (!empty($_FILES)) {
				try {
					$this->FILES = $this->util->files();
					if (get_class($this->FILES) == 'PostFiles') {
						$this->FILES = $this->FILES->FILES;
					}
				} catch (Exception $e) {
					Err::error($e->getMessage(), __LINE__, __FILE__);
				}
			}
		}

		final public function run()
		{
			$initialized = $this->initialize();

			foreach ($this->headers as $key => $value) {
				header("$key: $value");
			}
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
				'object' => $object,
				'errors' => $error,
				'code' => $this->httpResponseCode,
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
				'object' => $object,
				'code' => $this->httpResponseCode,
			];
		}
	}

	/**
	 * Класс для Страницы
	 */
	abstract class Page extends CoreObject
	{

		public $alias;
		public $title;

		public function __construct(Core $core)
		{
			parent::__construct($core);
			$this->source = WT_PAGES_PATH . $this->alias . '.tpl';
			if (!$this->alias) {
				$this->alias = $_GET['q'];
			}
			if (!$this->title) {
				$this->title = util::basename($this->alias) ?: $this->alias;
			}
			$this->smarty = new SmartyBC();
			$this->init();
		}

		public function init()
		{
			$this->smarty->setTemplateDir(WT_SMARTY_TEMPLATE_PATH . '/');
			$this->smarty->setCompileDir(WT_SMARTY_COMPILE_PATH . '/');
			$this->smarty->setConfigDir(WT_SMARTY_CONFIG_PATH . '/');
			$this->smarty->setCacheDir(WT_SMARTY_CACHE_PATH . '/');
			$this->smarty->assignGlobal('title', $this->title);
			$this->smarty->assignGlobal('page', $this);
			$this->smarty->assignGlobal('core', $this->core);
			$this->smarty->assignGlobal('user', $this->core->user);
			$this->smarty->assignGlobal('_GET', $_GET);
			$this->smarty->assignGlobal('_POST', $_POST);
			$this->smarty->assignGlobal('_REQUEST', $_REQUEST);
			$this->smarty->assignGlobal('_SERVER', $_SERVER);
			$this->smarty->assignGlobal('isAuthenticated', $this->core->isAuthenticated);
			$this->addModifier('user', '\core\model\Page::modifier_user');
			$this->addModifier('chunk', '\core\model\Page::modifier_chunk');
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

		public function forward($alias)
		{
			$this->source = WT_PAGES_PATH . $alias . '.tpl';
		}

		public function redirect($alias)
		{
			header("Location: $alias");
		}

		final public function render()
		{
			$this->beforeRender();
			if (!file_exists($this->source)) {
				header('HTTP/1.1 404 Not Found');
				readfile(WT_PAGES_PATH . '404.html');
				die;
			}
			$this->smarty->display($this->source);
		}

		public function setVar($name, $var, $nocache = FALSE)
		{
			$this->smarty->assign($name, $var, $nocache);
		}

		public function chunk($alias, $values = [])
		{
			$a = new Chunk($this->core, $alias, $values);
			return $a->render();
		}

		public static function modifier_chunk($alias, $values = [])
		{
			global $core;
			$a = new Chunk($core, $alias, $values);
			return $a->render();
		}

		public function errorPage($code = 404)
		{
			header("HTTP/1.1 $code Not Found");
			if (file_exists(WT_PAGES_PATH . $code . '.html')) {
				readfile(WT_PAGES_PATH . $code . '.html');
			} else {
				readfile(WT_PAGES_PATH . '404.html');
			}
			die;
		}

		public function beforeRender()
		{

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

		public function init()
		{
			$this->smarty->setTemplateDir(WT_SMARTY_TEMPLATE_PATH . '/');
			$this->smarty->setCompileDir(WT_SMARTY_COMPILE_PATH . '/');
			$this->smarty->setConfigDir(WT_SMARTY_CONFIG_PATH . '/');
			$this->smarty->setCacheDir(WT_SMARTY_CACHE_PATH . '/');
			$this->smarty->assign('title', $this->title);
			$this->smarty->assign('page', $this);
			$this->smarty->assign('core', $this->core);
			$this->smarty->assign('_GET', $_GET);
			$this->smarty->assign('_POST', $_POST);
			$this->smarty->assign('_REQUEST', $_REQUEST);
			$this->smarty->assign('_SERVER', $_SERVER);
			$this->addModifier('user', '\core\model\Page::modifier_user');
			$this->addModifier('chunk', '\core\model\Page::modifier_chunk');
		}
	}

	class Cache
	{

		private function getKey($a)
		{
			return md5(serialize($a));
		}

		public function setCache($key, $value, $expire = 0)
		{
			$name = $this->getKey($key) . '.cache.php';
			$v = var_export($value, 1);
			$expire = $expire ? $expire + time() : 0;
			$body = <<<PHP
<?php
	if($expire){
		if(time() > $expire){
			unlink(__FILE__);
			return null;
		}
	}

	return $v
?>
PHP;
			file_put_contents(WT_CACHE_PATH . $name, $body);
			return $value;
		}

		public function getCache($key)
		{
			$name = $this->getKey($key) . '.cache.php';
			if (file_exists(WT_CACHE_PATH . $name)) {
				return require WT_CACHE_PATH . $name;
			}
			return NULL;
		}

		public function removeCache($key)
		{
			$name = $this->getKey($key) . '.cache.php';
			if (file_exists(WT_CACHE_PATH . $name)) {
				unlink(WT_CACHE_PATH . $name);
			}
			return !file_exists(WT_CACHE_PATH . $name);
		}
	}

	/**
	 * класс с утилитами
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
						$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
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
							'http' => [
								'timeout' => $timeout,
								'header' => "User - Agent: Mozilla / 5.0\r\n",

							],
							'https' => [
								'timeout' => $timeout,
								'header' => "User - Agent: Mozilla / 5.0\r\n",
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
				'object' => $object,
			], 256);

		}

		public static function failure($msg, $object = [])
		{
			return json_encode([
				'success' => FALSE,
				'message' => $msg,
				'object' => $object,
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
			$arr = [
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
						$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
						$headers[$key] = $value;
					}
				}
				return $headers;
			}
			return [];
		}

		public static function headerJson()
		{
			@header("Content-type: application/json; charset=utf-8");
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
			$code = <<<PHP
<?php
	namespace core\ajax;
	use core\model\Ajax;
	class {$class} extends Ajax
	{
		{$method}
	}
	return '{$name}';
PHP;
			return $code;
		}

		public static function name2class($name)
		{
			$name = strtr($name, ['\\' => '_',
				'/' => '_']);
			$n = explode("_", $name);
			$n2 = [];
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
{block name='content'}
	
{/block}
TPL;
			return $code;
		}

		public static function makePageClass($name, $template = 'base')
		{
			$class = make::name2class($name);
			$code = <<<PHP
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

	return '$class';
PHP;
			return $code;
		}

		public static function makeTemplate($name, $template = 'base')
		{
		}

		public static function makeTable($name, $primaryKey = 'id')
		{
			$primaryKey = $primaryKey ?: 'id';
			$class = make::name2class($name);
			$code = <<<PHP
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
