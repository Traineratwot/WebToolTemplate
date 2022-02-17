<?php

	namespace model;

	use classes\tables\Users;
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
	use PDOExtended\PDOExtended;
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPSQLParser\PHPSQLParser;
	use SmartyBC;

	include_once(WT_MODEL_PATH . 'table.php');
	include_once(WT_MODEL_PATH . 'pdo.extended.php');
	include_once(WT_MODEL_PATH . 'postFiles.php');

	/**
	 * Основной класс
	 */
	final class Core implements ErrorPage
	{
		public    $db     = NULL;
		public    $user   = NULL;
		protected $_cache = [];
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
		{
			return new table();
		}

		/**
		 * @param $where
		 * @return Users
		 */
		public function getUser($where = [])
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
				$class = "core\classes\\tables\\$class";
				if (class_exists($class)) {
					return $class;
				} else {
					Err::fatal($class . " not exists", __LINE__, __FILE__);
				}
			}
		}

		/**
		 * @template T of \bdObject
		 * @param class-string<T> $class
		 * @param array           $where
		 * @param booean          $cache
		 * @return T
		 * @throws Exception
		 */
		public function getObject($class, $where = [], $cache = TRUE)
		{
			$class = Core::getClass($class);
			if (!$cache or empty($where)) {
				return new $class($this, $where);
			}
			$key = md5(serialize($where));
			if (!isset($this->_cache[$class]) or !isset($this->_cache[$class][$key])) {
				$this->_cache[$class][$key] = new $class($this, $where);
			}
			return $this->_cache[$class][$key];
		}

		/**
		 * @template T of \bdObject
		 * @param class-string<T> $class extends bdObject
		 * @param array           $where
		 * @return   [T]
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
			$errPage = new TmpPage($this, 'errors/' . $code);
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
		/**
		 * @var Core
		 */
		public Core $core;

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
									'primary'   => strtolower($row['COLUMN_KEY']) == "pri",
									'unique'    => strtolower($row['COLUMN_KEY']) == "uni",
								];
							}
						}
						return $output;
					}
				} catch (PDOException $e) {
					Err::error($e->getMessage());
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

		public function fromArray($data, $isNew = TRUE, $update = TRUE)
		{
			$this->data = array_merge($this->data, $data);
			if ($update) {
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
		public static function __set_state($array)
		{
			global $core;
			$a   = get_called_class();
			$obj = new $a($core);
			$obj->fromArray($array['data'], FALSE);
			return $obj;
		}
	}

	interface ErrorPage
	{
		public function errorPage($code, $msg);
	}