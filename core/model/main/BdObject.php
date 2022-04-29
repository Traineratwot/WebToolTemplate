<?php

	namespace model\main;

	use Exception;
	use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
	use PDO;
	use PDOException;
	use PHPSQLParser\PHPSQLParser;
	use const WT_TYPE_DB;

	/**
	 * Класс для работы с таблицей как с объектом
	 *
	 * не забудьте сохранить изменения save()
	 */
	abstract class BdObject extends CoreObject
	{
		public  $table      = '';
		public  $primaryKey = '';
		public  $isNew      = TRUE;
		public  $schema     = [];
		public  $_fields    = [];
		private $update     = [];
		private $data       = [];

		//--------------------------------------------------------

		public function __construct(Core $core, $where = [])
		{
			parent::__construct($core);
			try {
				$this->getSchema();
			} catch (Exception $e) {
				Err::fatal($e->getMessage());
			}
			try {
				if (!empty($where)) {
					if (!is_array($where)) {
						if (is_int($where) || is_numeric($where)) {
							$this->update([$this->primaryKey => $where]);
						} else {
							$this->update($where, 1);
						}
					} else {
						$this->update($where);
					}
				}
			} catch (PDOException $e) {
				Err::error($e->getMessage());
			}
			foreach ($this->_fields as $k => $v) {
				$this->data[$k] = $this->data[$k] ?: NULL;
			}
		}

		/**
		 * @param Boolean $cache
		 * @return void
		 */
		private function getSchema()
		{
			$data = Cache::call($this->table, function () {
				return $this->getColumnNames($this->table);
			},                  600, 'table');
			if (!empty($data)) {
				foreach ($data as $k => $v) {
					$this->_fields[$v['name']] = $k;
					$this->data[$v['name']]    = $v['default'];
					$this->schema[$v['name']]  = $v;
				}
			}
		}

		/**
		 * @param $table
		 * @return array
		 */
		private function getColumnNames($table)
		{
			if (WT_TYPE_DB === 'sqlite') {
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
								if (is_array($param) && $param['expr_type'] == 'data-type') {
									$data['data-type'] = $param;
								}
							}
							$default = (!isset($data['default']) || strtolower($data['default']) === 'null') ? NULL : trim($data['default'], '()');
							if (is_numeric($default)) {
								$default = (float)$default;
							}
							$output[$key] = [
								'name'      => $name,
								'default'   => $default,
								'comment'   => '',
								'type'      => $data['data-type']['base_expr'] ?: '',
								'maxLength' => (int)($data['data-type']['length']) ?: NULL,
								'null'      => (bool)$data['nullable'],
								'primary'   => isset($data['unique']) && $data['unique'],
								'unique'    => isset($data['default']) && $data['default'],
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
									'null'      => $row['IS_NULLABLE'] == "YES",
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
			return [];
		}

		/**
		 * @param $where
		 * @param $type
		 * @return $this
		 */
		private function update($where, $type = 0)
		{
			if (!$type) {
				$builder = new GenericBuilder();
				$query   = $builder->select()
								   ->setTable($this->table)
								   ->where()
				;
				foreach ($where as $key => $value) {
					$query->equals($key, $value);
				}
				$query  = $query->end();
				$sql    = $builder->write($query);
				$values = $builder->getValues();
				$values = self::prepareBinds($values);
				$sql    = self::prepareSql($sql, $this->table);
				$sql    = strtr($sql, $values);
			} else {
				$sql = <<<SQL
SELECT * FROM `{$this->table}` WHERE $where
SQL;
			}
			$q = $this->core->db->query($sql);
			if ($q) {
				$data = $q->fetch(PDO::FETCH_ASSOC);
				if (!empty($data)) {
					$this->fromArray($data, FALSE);
				}
			} else {
				Err::warning("invalid sql string: " . $sql);
			}
			return $this;
		}

		public static function prepareBinds($values)
		{
			foreach ($values as $k => $v) {
				if (!is_null($v) && mb_strtolower($v) != 'null' && !empty($v) && !is_numeric($v)) {
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

		/**
		 * @param      $data
		 * @param bool $isNew
		 * @param bool $update
		 * @return BdObject
		 */
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

		private function repair()
		{
			foreach ($this->data as $key => $value) {
				if (is_numeric($value)) {
					$this->data[$key] = (float)$value;
				}
				if ($this->data[$key] === 'NULL') {
					$this->data[$key] = NULL;
				}
			}
			foreach ($this->update as $key => $value) {
				if (is_numeric($value)) {
					$this->update[$key] = (float)$value;
				}
				if (stripos($value, 'NULL') === 0 && strlen($value) === 4) {
					$this->update[$key] = NULL;
				}
			}
		}

		/**
		 * @noinspection MagicMethodsValidityInspectio
		 * @param $array
		 * @return BdObject
		 */
		public static function __set_state($array)
		{
			global $core;
			$a   = static::class;
			$obj = new $a($core);
			$obj->fromArray($array['data'], FALSE);
			return $obj;
		}

		//--------------------------------------------------------

		/**
		 * @param array    $data
		 * @param string[] $where
		 * @return $this
		 */
		public function createFromArray($data, $where = [])
		{
			if (!empty($where)) {
				$where2 = [];
				foreach ($where as $key) {
					if ($data[$key]) {
						$where2[$key] = $data[$key];
					}
				}
				if ($where2) {
					$this->update($where2);
				} else {
					Err::fatal('Cannot create ' . self::class . ' from array');
				}
			} elseif (isset($this->data[$this->primaryKey]) && $this->data[$this->primaryKey]) {
				$this->update($this->data[$this->primaryKey]);
			}
			foreach ($data as $key => $val) {
				$this->set($key, $val);
			}
			$this->repair();
			return $this;
		}

		public function set($key, $value = NULL)
		{
			if (is_null($value) && !$this->schema[$key]['null']) {
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

		public function toArray()
		{
			return $this->data;
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
									 ->setValues($this->update)
					;

					$sql    = $builder->write($query);
					$values = $builder->getValues();
				} elseif (count($this->update) > 0) {
					$query = $builder->update()
									 ->setTable($this->table)
									 ->setValues($this->update)
									 ->where()
									 ->equals($this->primaryKey, $this->data[$this->primaryKey])
									 ->end()
					;

					$sql    = $builder->write($query);
					$values = $builder->getValues();
				}
				if ($sql) {
					$values = self::prepareBinds($values);
					$sql    = self::prepareSql($sql, $this->table);
					$sql    = strtr($sql, $values);
					//Err::info($sql);
					$this->core->db->exec($sql);
					if ($q !== FALSE && $this->isNew()) {
						$lastID = $this->core->db->lastInsertId();
						if ($lastID) {
							$this->data[$this->primaryKey] = $lastID;
						}
						$this->isNew = FALSE;
					} else {
						Err::error($sql);
					}
				}
				$this->update([$this->primaryKey => $this->data[$this->primaryKey]]);
			} catch (PDOException $e) {
				Err::error($e->getMessage());
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
					$this->core->db->exec($sql);
				}
			} catch (PDOException $e) {
				Err::error($e->getMessage());
			}
			return $this;
		}


		public function get($key, $default = NULL)
		{
			$this->repair();
			if (is_null($default) && isset($this->schema[$key])) {
				$default = $this->schema[$key]['null'] ? NULL : $this->schema[$key]['default'];
			}
			return $this->data[$key] ?: $default;
		}
	}