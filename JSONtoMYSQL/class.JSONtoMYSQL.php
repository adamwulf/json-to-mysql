<?php
/**
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class JSONtoMYSQL{

	public static $UPDATE = 'update';
	public static $INSERT = 'insert';

	protected $mysql;
	protected $table_cache;


	public function __construct(MySQLConn $mysql){
		$this->mysql = $mysql;
		$this->table_cache = [];
	}


    /**
     * will return the modifications needed
     * to the scheme to support inserting
     * the input json object
     *
     * returns an array of modifications
     * including:
     * creating tables
     * adding columns
     * modifying existing columns
     * adding foreign keys / foreign tables
     * flattening sub object into columns
     * @throws DatabaseException
     */
	public function save($json_obj, $tablename): MySQLResult
    {
		$table = $this->table($tablename);
		$table->validateTableFor($json_obj);
		return $table->save($json_obj);
	}

    /**
     * create or return a table for the input
     * tablename
     * @throws DatabaseException
     */
	public function table($tablename){
		if($this->tableExistsHuh($tablename)){
			return new ExistingMYSQLTable($this->mysql, $tablename);
		}else if(isset($this->table_cache[$tablename])){
			return $this->table_cache[$tablename];
		} else {
			$this->table_cache[$tablename] = new CreateMYSQLTable($this->mysql, $tablename);
			return $this->table_cache[$tablename];
		}
	}

    /**
     * helper method to determine if a table
     * already exists
     * @throws DatabaseException
     */
	protected function tableExistsHuh($tablename): bool
    {
		$sql = "SHOW TABLES LIKE '" . addslashes($tablename) . "'";
		$result = $this->mysql->query($sql);
		return $result->num_rows() > 0;
	}
	
	public function mysql() : MySQLConn{
		return $this->mysql;
	}
}




