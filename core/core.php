<?php

	namespace core;

	use core\classes\User;
	use Exception;
	use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
	use PDO;
	use PDOException;

	/**
	 * Основной класс
	 */
	class Core
	{
		public $db = NULL;
		public $user = NULL;

		public function __construct()
		{
			try {
				$this->db = new PDO(local_DSN, local_USER, local_PASS);
			} catch (PDOException $e) {
				Err::error($e->getMessage(), __LINE__, __FILE__);
			}
			if (isset($_COOKIE['authKey']) and $_COOKIE['authKey']) {
				$u = $this->getUser(['authKey' => $_COOKIE['authKey']]);
				if (!$u->isNew) {
					$this->user = &$u;
				} else {
					util::setCookie('authKey', NULL);
				}
			}
		}

		public function getUser($where = [])
		{
			return new User($this, $where);
		}

		public function getObject($class, $where = [])
		{
			if (class_exists($class)) {
				return new $class($this, $where);
			} else {
				$class = "core\classes\\$class";
				if (class_exists($class)) {
					return new $class($this, $where);
				} else {
					Err::fatal($class . " not exists");
				}
			}
		}

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
				Err::fatal($class . " not exists");
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
						if (is_int($where) OR is_numeric($where)) {
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

		public function repair()
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
					Err::warning('undefined field: "' . $key . '" in "' . $this->table . '"');
				}
			}
			foreach ($this->update as $key => $value) {
				if (!array_key_exists($key, $this->_fields)) {
					Err::warning('undefined field: "' . $key . '" in "' . $this->table . '"');
				}
			}
			return $this;
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
			}
			return $this;
		}

		public function getSchema($catch = TRUE)
		{
			if ($catch) {
				$c = CACHE_PATH . $this->table . '.json';
				if (file_exists($c)) {
					$data = json_decode(file_get_contents($c), 1);
				} else {
					$data = $this->getColumnNames($this->table);
					file_put_contents($c, json_encode($data, 256));
				}
			} else {
				$data = $this->getColumnNames($this->table);
			}
			foreach ($data as $k => $v) {
				$this->_fields[$v['name']] = $k;
				$this->data[$v['name']] = $v['default'];
				$this->schema[$v['name']] = $v;
			}
		}

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

		public function get($key, $default = NULL)
		{
			$this->repair();
			if (is_null($default)) {
				$default = $this->schema[$key]['default'];
			}
			return $this->data[$key] ?: $default;
		}

		public function isNew()
		{
			return $this->isNew;
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
					$sql = strtr($sql, $values);
					//Err::info($sql, __LINE__, __FILE__);
					$q = $this->core->db->exec($sql);
					if ($q !== FALSE and $this->isNew()) {
						$this->data[$this->primaryKey] = $this->core->db->lastInsertId();
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

		public function getColumnNames($table)
		{
			$sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :table";
			try {
				$stmt = $this->core->db->prepare($sql);
				$stmt->bindValue(':table', $table, PDO::PARAM_STR);
				$stmt->execute();
				$output = [];
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					if ($row['TABLE_SCHEMA'] != 'performance_schema') {
						$output[$row['ORDINAL_POSITION']] = [
							'name' => $row['COLUMN_NAME'],
							'key' => $row['COLUMN_KEY'] ?: NULL,
							'default' => $row['COLUMN_DEFAULT'] == 'null' ? NULL : $row['COLUMN_DEFAULT'],
							'comment' => $row['COLUMN_COMMENT'] ?? '',
							'type' => $row['DATA_TYPE'] ?? '',
							'maxLength' => $row['COLUMN_MAXIMUM_LENGTH'] ?? 0,
							'null' => $row['IS_NULLABLE'] == "YES" ? TRUE : FALSE,
						];
					}
				}
				return $output;
			} catch (PDOException $e) {
				Err::error($e->getMessage(), __LINE__, __FILE__);
			}
		}

		public static function prepareBinds($values)
		{
			foreach ($values as $k => $v) {
				if (!is_null($v) and $v != 'NULL' and $v != 'null' and $v != NULL and !is_numeric($v)) {
					$values[$k] = json_encode($v, 256);
				}
			}
			return $values;
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
	}