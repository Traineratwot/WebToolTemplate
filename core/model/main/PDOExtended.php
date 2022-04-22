<?php

	namespace model\main;

	use PDO;

	/** Расширение для класса PDO
	 *
	 * @version       2.0
	 * @author        Insys <intsystem88@gmail.com>
	 * @copyright (c) 2013, Insys
	 * @link          https://github.com/InSys/pdo-extended
	 * @license       http://opensource.org/licenses/GPL-2.0 The GNU General Public License (GPL-2.0)
	 */
	require_once __DIR__ . '/PDOExtendedStatement.php';
	require_once __DIR__ . '/PDOExtendedPoolStatement.php';

	class PDOExtended extends PDO
	{
		private $query_count = 0;
		const DSN_REGEX = '/^(?P<user>\w+)(:(?P<password>\w+))?@(?P<host>[.\w]+)(:(?P<port>\d+))?\\\\(?P<database>\w+)$/im';
		/**
		 * @var array|false
		 */
		public  $dsn_info;
		public $dsn;

		public function __construct($dsn, $username = NULL, $password = NULL, $driverOptions = [])
		{
			parent::__construct($dsn, $username, $password, $driverOptions);
			$this->dsn_info = $this->ParseDsn($dsn, $username, $password);
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['model\main\PDOExtendedStatement', [$this]]);
		}

		protected function ParseDsn($dsn, $username, $password)
		{
			$result = [
				'driver'   => '',
				'user'     => '',
				'password' => '',
				'host'     => 'localhost',
				'port'     => 3306,
				'database' => '',
			];
			if (strlen($dsn) == 0) {
				return FALSE;
			}
			$a = explode(':', $dsn);
			if ($a > 1) {
				$result['driver'] = $a[0];
			} else {
				return FALSE;
			}
			$b = explode(';', $a[1]);
			foreach ($b as $c) {
				$c                = explode('=', $c);
				$this->dsn[$c[0]] = $c[1];
			}
			$this->dsn['user'] = $username;
			$this->dsn['pass'] = $password;
			return $result;
		}

		public function query_count_increment()
		{
			$this->query_count++;
		}

		public function __get($name)
		{
			if ($name == 'query_count') {
				return $this->query_count;
			}
			return NULL;
		}

		public function __set($name, $value)
		{
			return FALSE;
		}

		public function __isset($name)
		{
			if ($name == 'query_count') {
				return TRUE;
			}
			return FALSE;
		}

		/**
		 * @param string $statement SQL request
		 * @return bool|PDOExtendedStatement
		 */
		public function query($statement, ...$a)
		{
			$this->query_count_increment();
			return parent::query($statement, ...$a); // TODO: Change the autogenerated stub
		}

		/**
		 * @param $statement
		 * @param ...$a
		 * @return bool|PDOExtendedStatement
		 */
		public function prepare($statement, ...$a)
		{
			return parent::prepare($statement, ...$a); // TODO: Change the autogenerated stub
		}

		/**
		 * @param string $statement SQL request
		 * @return void
		 */
		public function exec($statement)
		{
			$this->query_count_increment();
			parent::exec($statement); // TODO: Change the autogenerated stub
		}

		/**
		 * Проверяет существование таблицы в базе
		 * @param string $table
		 * @return bool
		 */
		public function tableExists($table)
		{
			if ($this->dsn_info['driver'] == 'sqlite') {
				return (bool)(int)$this->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'")->fetchAll(PDO::FETCH_NUM);
			} else {
				return (bool)(int)$this->query("SHOW TABLES LIKE '{$table}'")->fetchAll(PDO::FETCH_NUM);
			}
		}

		/**
		 * @param       $statement
		 * @param array $driver_options
		 * @return bool|PDOExtendedPoolStatement
		 */
		public function poolPrepare($statement, array $driver_options = [])
		{

			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['model\main\PDOExtendedPoolStatement', [$this]]);
			$r = parent::prepare($statement, $driver_options);
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['model\main\PDOExtendedStatement', [$this]]);
			return $r;
		}

	}

