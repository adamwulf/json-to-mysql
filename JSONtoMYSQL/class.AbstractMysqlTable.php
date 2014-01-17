<?
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
	
	/**
	 * initialize with the table name and a connection
	 * to the database
	 */
	public function __construct($mysql, $tablename){
		$this->mysql = $mysql;
		$this->tablename = $tablename;
	}

	/**
	 * this method should be called to make sure that
	 * the table exists and could operate on the input
	 * data if needed
	 *
	 * this will create the table if needed, and will create
	 * any columns necessary for existing tables
	 */
	abstract public function validateTableFor($json_data);

	/**
	 * will insert or update the table for the input
	 * json object. the row will update if the input
	 * does match a value in the primary column, otherwise
	 * will insert a new row
	 */
	abstract public function save($json_data);


	/**
	 * helper method to make a mysql safe column name
	 * from any input string
	 */
	protected function getColumnNameForKey($key){
		return preg_replace('/[^a-zA-Z0-9_]/', '', $key);
	}

	/**
	 * will determine a valid mysql column type from
	 * the input variable value
	 */
	protected function getMysqlTypeForValue($val){
		if(preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $val)){
			return "DATETIME";
		}else if(is_string($val)){
			return "TEXT";
		}else if(is_bool($val)){
			return "TINYINT";
		}else if(is_int($val)){
			return "BIGINT";
		}else if(is_double($val) || is_float($val) || is_real($val)){
			return "DOUBLE";
		}else{
			echo "unknown mysql type for: " . gettype($val) . "\n";
		}
	}
}


?>