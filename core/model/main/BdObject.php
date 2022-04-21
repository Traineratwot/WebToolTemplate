<?php

	namespace model\main;

	use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
	use PDO;
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