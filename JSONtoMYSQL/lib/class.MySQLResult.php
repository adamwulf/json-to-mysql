<?php
/**
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class MySQLResult{

	private $result;
	private $insert_id;
	private $affected_rows;
	private $offset;

	public function __construct($link, $result){
		$this->result = $result;
		$this->insert_id = @mysqli_insert_id($link);
		$this->affected_rows = @mysqli_affected_rows($link);
		$this->offset = 0;
	}


	function num_rows() : int {
		return mysqli_num_rows($this->result);
	}

	function fetch_array() : ?array {
		if($this->offset < $this->num_rows()){
			$this->offset += 1;
			return mysqli_fetch_array($this->result, MYSQLI_ASSOC);
		}
		return null;
	}

	function peek_array() : ?array {
		$offset = $this->offset;
		$ret = $this->fetch_array();

		if($this->offset != $offset){
			// restore pointer to previous record
			mysqli_data_seek($this->result, $offset);
			$this->offset = $offset;
		}

		return $ret;
	}

	function insert_id() : int {
		return $this->insert_id;
	}

	function affected_rows() : int {
		return $this->affected_rows;
	}

	function has_next() : bool {
		if($this->offset < $this->num_rows()){
			return true;
		}else{
			return false;
		}
	}

	function rewind() : bool {
		if($this->num_rows() > 0){
			$this->offset = 0;
			return mysqli_data_seek($this->result, 0);
		}
		return false;
	}
}

