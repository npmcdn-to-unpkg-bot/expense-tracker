<?php

namespace Core;

class DB extends \PDO {

	private static $dbInstance;

	protected $queryCount = 0;
	protected $execTime = 0;
	private   $loggerCallback = null;

	public static function getInstance($driverOptions = array(), $loggerCallback = null) {
		if (!self::$dbInstance) {
//			self::$dbInstance = new DB($driverOptions, array('App', 'logDB'));
			self::$dbInstance = new DB($driverOptions, null);
		}
		return self::$dbInstance;
	}

	public function __construct ($driverOptions, $loggerCallback) {
		$connectionString = 'mysql:host=' . DB_SERVER . ':3306;dbname=' . DB_NAME;
		parent::__construct($connectionString, DB_USER, DB_PASS, $driverOptions);
		$this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->exec('set character_set_client="utf8"');
        $this->exec('set character_set_results="utf8"');
        $this->exec('set collation_connection="utf8_general_ci"');

		if (!$this->getAttribute(\PDO::ATTR_PERSISTENT)) {
			$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('\Core\dbPDOStatement', array($this)));
		}
		$this->loggerCallback = $loggerCallback;
	}

	public function incrementQueryCount() {
		$this->queryCount++;
	}

	public function getQueryCount() {
		return $this->queryCount;
	}

	public function addExecTime($time) {
		$this->execTime += $time;
	}

	public function getExecTime() {
		return $this->execTime;
	}

	public function log() {
		if (!is_null($this->loggerCallback)) {
			$args = func_get_args();
			call_user_func_array($this->loggerCallback, $args);
		}
	}

	public function exec($sql) {
		$this->log($sql, 'Query (PDO->exec())');
		$this->incrementQueryCount();

		$start = microtime(true);
		$return = parent::exec($sql);
		$finish = microtime(true);
		$this->addExecTime($finish - $start);
		return $return;
	}

	public function query() {
		$this->incrementQueryCount();
		$args = func_get_args();
		$this->log($args, 'Query (PDO->query())');

		$start = microtime(true);
		$return = call_user_func_array(array($this, 'parent::query'), $args);
		$finish = microtime(true);
		$this->addExecTime($finish-$start);
		return $return;
	}

}


?>
