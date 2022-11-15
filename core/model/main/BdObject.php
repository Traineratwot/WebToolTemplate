<?php

	namespace model\main;

	use Exception;
	use PDO;
	use PDOException;

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

		//--------------------------------------------------------

		public function __construct(Core $core, $where = [])
		{
			parent::__construct($core);
			$this->schema = $core->db->getScheme($this->table);

			$this->pd = $core->db->table($this->table);
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
			} catch (Exception $e) {
				Err::error($e->getMessage());
			}
			foreach ($this->schema->columns as $v) {
				$n              = $v->getName();
				$this->data[$n] = $this->data[$n] ?? NULL;
			}
			$this->schema = $this->schema->toArray();
		}

		/**
		 * @param $where
		 * @param $type
		 * @return $this
		 */
		private function update($where, $type = 0)
		{
			if (!$type) {

				$query = $this->pd->select()->where();
				foreach ($where as $key => $value) {
					$query->and();
					$query->eq($key, $value);
				}
				$sql = $query->end()->toSql();
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

		/**
		 * @param      $data
		 * @param bool $isNew
		 * @param bool $update
		 * @return BdObject
		 */
		public function fromArray($data, $isNew = TRUE, $update = FALSE)
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

		public function toArray()
		{
			return $this->data;
		}

		//--------------------------------------------------------

		/**
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
			if (!$this->schema[$key]) {
				Err::warning('Not found key :"' . $key . '" in table "' . $this->table . '"');
			}
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
			return $this;
		}

		public function save()
		{
			try {
				$sql = NULL;
				if (count($this->update) > 0) {
					if ($this->isNew()) {
						$sql = $this->pd->insert()
										->setData($this->update)
										->toSql()
						;
					} else {
						$sql = $this->pd->update()
										->setData($this->update)
										->where()
										->eq($this->primaryKey, $this->data[$this->primaryKey])
										->end()->toSql()
						;
					}
					if ($sql) {
						//Err::info($sql);
						$q = $this->core->db->exec($sql);
						if ($q === FALSE) {
							Err::fatal($sql);
						}
						if ($this->isNew()) {
							$lastID = $this->core->db->lastInsertId();
							if ($lastID) {
								$this->data[$this->primaryKey] = $lastID;
							}
							$this->isNew = FALSE;
						}
					}
				}
				if (isset($this->data[$this->primaryKey])) {
					$this->update([$this->primaryKey => $this->data[$this->primaryKey]]);
				}
			} catch (Exception $e) {
				Err::fatal($e->getMessage(), 0, 0, $e);
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
				$default = ($this->schema[$key]['null'] ?? NULL) ? NULL : $this->schema[$key]['default'];
			}
			return $this->data[$key] ?: $default;
		}

		public function getID()
		{
			return $this->get($this->primaryKey);
		}

		public function isDirty(string $key = NULL)
		: bool
		{
			if ($key === NULL) {
				return count($this->update) > 0;
			}
			return array_key_exists($key, $this->update);
		}
	}