<?php

if(!defined('JSONTOMYSQL_LOCKED')){
	define('JSONTOMYSQL_LOCKED', false);
}

/**
 * represents a table that may or may not exist
 * in MySQL database yet
 *
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
abstract class AbstractMysqlTable{

	protected $tablename;
	
	protected $mysql;
	protected $locked;
	
	// a cache of the primary index column name
	protected $primary;

	/**
	 * initialize with the table name and a connection
	 * to the database
	 */
	public function __construct($mysql, $tablename, $primary){
		$this->mysql = $mysql;
		$this->tablename = $tablename;
		$this->primary = $this->getColumnNameForKey($primary);
		$this->locked = JSONTOMYSQL_LOCKED;
	}
	
	public function isLocked(){
		return $this->locked;
	}

	public function setLocked($lock){
		$this->locked = $lock;
	}

	public function name(){
		return $this->tablename;
	}

	abstract public function primaryColumn();

	/**
	 * this method should be called to make sure that
	 * the table exists and could operate on the input
	 * data if needed
	 *
	 * this will create the table if needed, and will create
	 * any columns necessary for existing tables
	 *
	 * $typeForColName($data, $value) can return a mysql data type
	 * to override JSONtoMYSQL's auto type.
	 */
	abstract public function validateTableFor($json_data, Closure $typeForColName = null);

	/**
	 * will insert or update the table for the input
	 * json object. the row will update if the input
	 * does match a value in the primary column, otherwise
	 * will insert a new row
	 */
	abstract public function save($json_data);

	abstract public function update($json_data);

	abstract public function insert($json_data);

	abstract public function find($json_obj = array(), $ops=false);

	abstract public function delete($json_obj);

	abstract public function truncate();

	/**
	 * helper method to make a mysql safe column name
	 * from any input string
	 */
	public function getColumnNameForKey($key){
		return preg_replace('/[^a-zA-Z0-9_]/', '', $key);
	}

	/**
	 * will determine a valid mysql column type from
	 * the input variable value
	 */
	protected function getMysqlTypeForValue($val){
		if(preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $val)){
			return "DATETIME";
		}else if(preg_match('/\d{4}-\d{2}-\d{2}/', $val)){
			return "DATE";
		}else if(is_string($val)){
			return "TEXT";
		}else if(is_bool($val)){
			return "TINYINT";
		}else if(is_int($val)){
			return "BIGINT";
		}else if(is_double($val) || is_float($val) || is_real($val)){
			return "DOUBLE";
		}else if(!is_null($val)){
			error_log("unknown mysql type for: " . gettype($val) . "\n");
		}
	}
}


?>