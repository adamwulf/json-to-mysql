<?
/**
 * the class represents a table that already
 * exists in the Mysql database
 *
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class ExistingMYSQLTable extends AbstractMysqlTable{

	// a cache of the primary index column name
	private $primary;

	/**
	 * make sure to cache our primary column name
	 * to ease future operations
	 */
	public function validateTableFor($data){
		$sql = "show index from " . addslashes($this->tablename) . " where Key_name = 'PRIMARY' ;";
		$result = $this->mysql->query($sql);
		$arr = $result->fetch_array();
		$this->primary = $arr["Column_name"];
	}
	
	
	/**
	 * will save the input json object contains
	 * a value for the primary column or will
	 * insert a new row
	 */
	public function save($json_obj){
		$primary = $this->primary;
		if(isset($json_obj->$primary)){
			$res = $this->find(array($primary => $json_obj->$primary));
			if($res->num_rows()){
				// already exists with this primary key value, update it
				$this->update($json_obj);
				return JSONtoMYSQL::$UPDATE;
			}else{
				// doesn't exist yet, insert
				$this->insert($json_obj);
				return JSONtoMYSQL::$INSERT;
			}
		}else{
			$this->insert($json_obj);
			return JSONtoMYSQL::$INSERT;
		}
	}
	
	
	/**
	 * returns a MysqlResult for a SELECT query
	 * that tries to find all values in the table
	 * that match the input json object
	 */
	public function find($json_obj){
		$where = "";
		
		foreach($json_obj as $key => $value){
			if(is_array($value)){
/* 				echo "need to handle array subdata\n"; */
			}else if(is_object($value)){
/* 				echo "need to handle object subdata\n"; */
			}else{
				$colname = $this->getColumnNameForKey($key);
				if(strlen($where)){
					$where .= " AND ";
				}
				$where .= "`" . $colname . "`";
				if($this->getMysqlTypeForValue($value) == "TEXT"){
					$where .= " LIKE '" . addslashes($value) . "'";
				}else{
					$where .= " = '" . addslashes($value) . "'";
				}
			}
		}
		$sql = "SELECT * FROM `" . addslashes($this->tablename) . "` WHERE " . $where;
		return $this->mysql->query($sql);
	}
	
	/*
	 * finds the rows just like the find() method
	 * and then deletes all of them
	 */
	public function delete($json_obj){
		$where = "";
		
		foreach($json_obj as $key => $value){
			if(is_array($value)){
/* 				echo "need to handle array subdata\n"; */
			}else if(is_object($value)){
/* 				echo "need to handle object subdata\n"; */
			}else{
				$colname = $this->getColumnNameForKey($key);
				if(strlen($where)){
					$where .= " AND ";
				}
				$where .= "`" . $colname . "`";
				if($this->getMysqlTypeForValue($value) == "TEXT"){
					$where .= " LIKE '" . addslashes($value) . "'";
				}else{
					$where .= " = '" . addslashes($value) . "'";
				}
			}
		}
		$sql = "DELETE FROM `" . addslashes($this->tablename) . "` WHERE " . $where;
		return $this->mysql->query($sql);
	}
	
	/**
	 * will update a row in the database for
	 * the input object by comparing its value
	 * for the primary column name
	 */
	protected function update($json_obj){
		$set = "";
		
		$primary_val = 0;
		
		foreach($json_obj as $key => $value){
			if($key == $this->primary){
				$primary_val = $value;
				continue;
			}
			if(is_array($value)){
/* 				echo "need to handle array subdata\n"; */
			}else if(is_object($value)){
/* 				echo "need to handle object subdata\n"; */
			}else{
				$colname = $this->getColumnNameForKey($key);
				if(strlen($set)){
					$set .= ", ";
				}
				$set .= "`" . $colname . "`";
				$set .= " = '" . addslashes($value) . "'";
			}
		}
		if(strlen($set)){
			$sql = "UPDATE `" . addslashes($this->tablename) . "` SET "
				 . $set . " WHERE `" . $this->primary . "`='" . addslashes($primary_val) . "';";
			
			$result = $this->mysql->query($sql);
		}
	}
	
	/**
	 * this method will attempt to add a new row
	 * to the table with all of the values
	 * of the input json object
	 */
	protected function insert($json_obj){
		$fields = "";
		$values = "";
		
		foreach($json_obj as $key => $value){
			if(is_array($value)){
/* 				echo "need to handle array subdata\n"; */
			}else if(is_object($value)){
/* 				echo "need to handle object subdata\n"; */
			}else{
				$colname = $this->getColumnNameForKey($key);
				if(strlen($fields)){
					$fields .= ",";
					$values .= ",";
				}
				$fields .= "`" . $colname . "`";
				$values .= "'" . addslashes($value) . "'";
			}
		}
	
		if(strlen($fields)){
			$sql = "INSERT INTO `" . addslashes($this->tablename) . "` "
				 . "(" . $fields . ") VALUES (" . $values . ")";
			
			$result = $this->mysql->query($sql);
		}
	}

	/**
	 * returns true if the input column name already exists
	 * in the table, or false otherwise
	 */
	protected function columnExistsInTableHuh($columnname){
		$sql = "SHOW COLUMNS FROM `" . addslashes($this->tablename) . "` LIKE '" . addslashes($columnname) . "'";
		$result = $this->mysql->query($sql);
		return $result->num_rows() > 0;
	}	

	

}

?>