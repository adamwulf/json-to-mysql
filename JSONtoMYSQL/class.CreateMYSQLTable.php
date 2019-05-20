<?php
/**
 * this will create the table to match the input
 * json object, and then will defer to the
 * ExistingMYSQLTable class for all other operations
 *
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class CreateMYSQLTable extends ExistingMYSQLTable{

	public function __construct($mysql, $tablename, $primary="id"){
		parent::__construct($mysql, $tablename, $primary);
	}

	/**
	 * this will create the input table including
	 * the appropriate primary column as specified
	 * in the constructor
	 */
	public function validateTableFor($data, Closure $typeForColName = null, Closure $nullabilityForColName = null){
		if(JSONTOMYSQL_LOCKED){
			throw new Exception("JsonToMysql is locked. Cannot create new table " . $this->tablename);
		}
	
		$colstr = "";
		
		foreach($data as $key => $value){
			if(is_array($value)){
				echo "need to handle array subdata\n";
			}else if(is_object($value)){
				echo "need to handle object subdata\n";
			}else if($key != $this->primary){
				$colname = $this->getColumnNameForKey($key);
				$type = $this->getMysqlTypeForValue($value);
				$nullable = false;

				if($typeForColName){
					$typeInfo = $typeForColName($colname, $value, $type);
					if(is_array($typeInfo)){
						$type = $typeInfo[0];
						$nullable = $typeInfo[1];
					}else{
						$type = $typeInfo;
					}
				}
				
				if(!$type){
					error_log(" - unknown type for column " . $colname);
				}

				$nullability = $nullable ? " NULL " : " NOT NULL ";
				
				$colstr .= "  `" . $colname . "` " . $type . $nullability . ",";
			}
		}
	
	
		$sql = "CREATE TABLE IF NOT EXISTS `" . addslashes($this->tablename) . "` ("
			 . "  `" . $this->primary . "` bigint(20) NOT NULL auto_increment,"
			 . $colstr
			 . "  PRIMARY KEY  (`" . $this->primary . "`)"
			 . ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
		
		$result = $this->mysql->query($sql);
	
		$issues = [];
		$issues[] = ["notice" => "created table"];
		return $issues;
	}
	
	/**
	 * returns a MysqlResult for a SELECT query
	 * that tries to find all values in the table
	 * that match the input json object
	 */
	public function find($json_obj = array(), $ops=false, $orders=false){
		$sql = "SELECT 0 LIMIT 0";
		return $this->mysql->query($sql);
	}

	public function delete($json_obj){
		// noop
		return false;
	}

	public function truncate(){
		// noop
		return false;
	}

}

?>