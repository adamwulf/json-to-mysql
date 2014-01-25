<?
/**
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class JSONtoMYSQL{

	public static $UPDATE = 'update';
	public static $INSERT = 'insert';


	protected $mysql;


	public function __construct($mysql){
		$this->mysql = $mysql;
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
	 */
	public function save($json_obj, $tablename){
		$table = $this->table($tablename);
		$table->validateTableFor($json_obj);
		return $table->save($json_obj);
	}
	
	/**
	 * create or return a table for the input
	 * tablename
	 */
	public function table($tablename){
		if($this->tableExistsHuh($tablename)){
			return new ExistingMYSQLTable($this->mysql, $tablename);
		}else{
			return new CreateMYSQLTable($this->mysql, $tablename);
		}
	}
	
	/**
	 * helper method to determine if a table
	 * already exists
	 */
	protected function tableExistsHuh($tablename){
		$sql = "SHOW TABLES LIKE '" . addslashes($tablename) . "'";
		$result = $this->mysql->query($sql);
		return $result->num_rows() > 0;
	}
	
	public function mysql(){
		return $this->mysql;
	}
}




?>