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

    /** @var string $tablename */
	protected $tablename;

	/** @var MySQLConn $mysql */
	protected $mysql;

	/** @var bool $locked */
	protected $locked;
	
    /** @var ?string $primary a cache of the primary index column name */
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
	
	public function isLocked(): bool
    {
		return $this->locked;
	}

	public function setLocked(bool $lock): void
    {
		$this->locked = $lock;
	}

	public function name(): string
    {
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
	abstract public function save($json_data) : MySQLResult;

	abstract public function update($json_data) : MySQLResult;

	abstract public function insert($json_data) : MySQLResult;

    abstract public function find(array $json_obj = array(), array $ops = null, array $orders = null) : MySQLResult;

	abstract public function delete($json_obj) : MySQLResult;

	abstract public function truncate() : MySQLResult;

    abstract public function addUniqueIndexTo(array $columns, string $name): void;

    abstract public function addIndexTo(array $columns, string $name) : void;

	/**
	 * helper method to make a mysql safe column name
	 * from any input string
	 */
	public function getColumnNameForKey(string $key) : string {
		return preg_replace('/[^a-zA-Z0-9_]/', '', $key);
	}

    /**
     * will determine a valid mysql column type from
     * the input variable value
     * @param mixed $val
     * @return string
     * @throws DatabaseException
     */
	protected function getMysqlTypeForValue($val) : ?string {
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
			throw new DatabaseException("unknown mysql type for: " . gettype($val));
		}
		return null;
	}
}


