<?php

namespace Core;

class dbPDOStatement extends \PDOStatement {
	
	protected $pdo;
	private $params = array();
	protected static $type_map = array(
		\PDO::PARAM_BOOL => "PDO::PARAM_BOOL",
		\PDO::PARAM_INT => "PDO::PARAM_INT",
		\PDO::PARAM_STR => "PDO::PARAM_STR",
		\PDO::PARAM_LOB => "PDO::PARAM_LOB",
		\PDO::PARAM_NULL => "PDO::PARAM_NULL"
	);
	
	protected function __construct(db $pdo) {
		$this->pdo = $pdo;
	}
	
	public function execute($inputParameters = null) {
		if (!empty($this->params)) {
			$this->pdo->log($this->params, 'Parameters');
		}  
	
		if (!empty($inputParameters)) {
			$this->pdo->log($inputParameters, 'Parameters');
		}      
		$this->pdo->incrementQueryCount();
		
		$start = microtime(true);
		$return = parent::execute($inputParameters);
		$finish = microtime(true);
		$this->pdo->addExecTime($finish - $start);
		$this->pdo->log(substr((string)($finish - $start), 0, 8) . ', ' . $this->queryString, 'Query (PDOStatement->execute())');    
		return $return;
	}
	
	public function bindValue($pos, $value, $type = \PDO::PARAM_STR) {
		$type_name = isset(self::$type_map[$type]) ? self::$type_map[$type] : '(default)';
		$this->params[] = array($pos, $value, $type_name);
		$return = parent::bindValue($pos, $value, $type);
		return $return;    
	}

}

?>