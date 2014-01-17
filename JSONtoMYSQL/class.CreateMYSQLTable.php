<?
/**
 * this will create the table to match the input
 * json object, and then will defer to the
 * ExistingMYSQLTable class for all other operations
 *
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class CreateMYSQLTable extends ExistingMYSQLTable{


	private $primary;

	public function __construct($mysql, $tablename, $primary="id"){
		parent::__construct($mysql, $tablename);
		$this->primary = $this->getColumnNameForKey($primary);
	}

	/**
	 * this will create the input table including
	 * the appropriate primary column as specified
	 * in the constructor
	 */
	public function validateTableFor($data){
		$colstr = "";
		
		foreach($data as $key => $value){
			if(is_array($value)){
				echo "need to handle array subdata\n";
			}else if(is_object($value)){
				echo "need to handle object subdata\n";
			}else if($key != $this->primary){
				$colname = $this->getColumnNameForKey($key);
				$type = $this->getMysqlTypeForValue($value);
				$colstr .= "  `" . $colname . "` " . $type . " NOT NULL,";
			}
		}
	
	
		$sql = "CREATE TABLE IF NOT EXISTS `" . addslashes($this->tablename) . "` ("
			 . "  `" . $this->primary . "` bigint(20) NOT NULL auto_increment,"
			 . $colstr
			 . "  PRIMARY KEY  (`" . $this->primary . "`)"
			 . ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
		
		$result = $this->mysql->query($sql);
	
		parent::validateTableFor($data);
	}
	
	/**
	 * returns a MysqlResult for a SELECT query
	 * that tries to find all values in the table
	 * that match the input json object
	 */
	public function find($json_obj = array()){
		$sql = "SELECT 0 LIMIT 0";
		return $this->mysql->query($sql);
	}

	public function delete($json_obj){
		// noop
		return false;
	}

}

?>