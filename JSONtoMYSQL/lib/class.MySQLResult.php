<?php
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
		$this->insert_id = @mysqli_insert_id($link);
		$this->affected_rows = @mysqli_affected_rows($link);
	}

	
	function num_rows(){
		return mysqli_num_rows($this->result);
	}

	function fetch_array(){
		return mysqli_fetch_array($this->result, MYSQLI_ASSOC);
	}
	
	function insert_id(){
		return $this->insert_id;
	}

	function affected_rows(){
		return $this->affected_rows;
	}
}

?>