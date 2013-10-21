<?
/**
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class MySQLResult{

	private $result;
	private $insert_id;
	private $affected_rows;


	public function __construct($link, $result){
		$this->result = $result;
		$this->insert_id = @mysql_insert_id($link);
		$this->affected_rows = @mysql_affected_rows($link);
	}

	
	function num_rows(){
		return mysql_num_rows($this->result);
	}

	function fetch_array(){
		return mysql_fetch_array($this->result, MYSQL_ASSOC);
	}
	
	function insert_id(){
		return $this->insert_id;
	}

	function affected_rows(){
		return $this->affected_rows;
	}
}

?>