<?php
/**
 * the class represents a table that already
 * exists in the Mysql database
 *
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class ExistingMYSQLTable extends AbstractMysqlTable{

	private $fields;

	public function __construct($mysql, $tablename, $primary="id"){
		parent::__construct($mysql, $tablename, $primary);
		$this->fields = array();
	}
	
	public function primaryColumn() : string {
		return $this->primary;
	}

    /**
     * make sure to cache our primary column name
     * to ease future operations
     * @throws DatabaseException
     */
	public function validateTableFor($data, Closure $typeForColName = null, Closure $nullabilityForColName = null) : array {
		$issues = [];
		
		if(!count($this->fields)){
			// pull the primary column from the database
			$sql = "show index from " . addslashes($this->tablename) . " where Key_name = 'PRIMARY' ;";
			$result = $this->mysql->query($sql);
			$arr = $result->fetch_array();
			$this->primary = $arr["Column_name"];
			
			// fetch all columns and types
			$this->fields = array();
			$sql = "SHOW FIELDS FROM  `" . addslashes($this->tablename) . "`";
			$result = $this->mysql->query($sql);
			while($row = $result->fetch_array()){
				$field = array("name" => $row["Field"], "type" => $row["Type"], "nullable" => $row["Null"] == "YES");
				$this->fields[] = $field;
			}
		}
		
		$missing = array();
		foreach($data as $key => $value){
			if(!is_array($value) && !is_object($value)){
				$columnname = $this->getColumnNameForKey($key);
				$found = false;
				foreach($this->fields as $field){
					if($field["name"] == $columnname){
						$found = $field;
						break;
					}
				}
				$type = $this->getMysqlTypeForValue($value);
				$nullable = null;

				if($typeForColName){
					$typeInfo = $typeForColName($columnname, $value, $type);
					if(is_array($typeInfo)){
						$type = $typeInfo[0];
						$nullable = $typeInfo[1];
					}else{
						$type = $typeInfo;
					}
				}
				
				if(!$type && !$found){
					$issues[] = ["column" => $columnname, "error" => "unknown type"];
                    /** @noinspection ForgottenDebugOutputInspection */
                    error_log(" - unknown type for column " . $columnname . " when validating table " . $this->name());
				}
				
				if(!$found){
					$issues[] = ["column" => $columnname, "error" => "missing column " . $type . " null? " . $nullable];
					$missing[] = array("name" => $columnname, "type" => $type, "nullable" => $nullable);
				}else{
					$type = strtoupper($type);
					$foundType = strtoupper($found["type"]);
					if($type && $foundType && strpos($type, $foundType) !== 0 && strpos($foundType, $type) !== 0){
						$issues[] = ["column" => $columnname, "error" => "invalid type: should be " . $type . ", but is " . $found["type"]];
					}
					if($nullable && !$found["nullable"]){
						$issues[] = ["column" => $columnname, "error" => "invalid nullability: should nullable, but isn't"];
					}else if($nullable === false && $found["nullable"]){
						$issues[] = ["column" => $columnname, "error" => "invalid nullability: shouldn't nullable, but is"];
					}
				}
			}
		}
		
		if($this->isLocked() && count($missing)){
			$colnames = array_map(function($field){
				return $field["name"];
			}, $missing);
			throw new DatabaseException("JsonToMysql is locked. Cannot create columns " . join(',', $colnames) . " in table " . $this->tablename);
		}else if(count($missing)){
			foreach($missing as $field){
				$nullability = $field["nullable"] ? " NULL " : " NOT NULL ";
				$sql = "ALTER TABLE `" . addslashes($this->tablename) . "` ADD `" . addslashes($field["name"]) . "` " . $field["type"] . $nullability . ";";
				$this->mysql->query($sql);
				$this->fields[] = $field;
			}
		}
		
		return $issues;
	}


    /**
     * @param array<string> $columns
     * @param string $name
     * @throws DatabaseException
     */
    public function addUniqueIndexTo(array $columns, string $name) : void {
		$sql = "show index from " . addslashes($this->tablename) . " where Key_name = '" . addslashes($name) . "' ;";
		$result = $this->mysql->query($sql);

		if(!$result->num_rows()){
			
			$cols = "";
			foreach($columns as $column){
				if(strlen($cols)){
					$cols .= ", ";
				}
				if(is_string($column)){
					$cols .= "`" . addslashes($column) . "`";
				}else if(is_array($column)){
					$cols .= "`" . addslashes($column[0]) . "`(" . ((int)$column[1]) . ")";
				}
			}
			
			$sql = "ALTER TABLE `" . addslashes($this->tablename) . "` ADD UNIQUE `" . addslashes($name) . "` (" . $cols . ");";
			$this->mysql->query($sql);
		}
	}

    /**
     * @param array<string> $columns
     * @param string $name
     * @throws DatabaseException
     */
    public function addIndexTo(array $columns, string $name) : void {
		$sql = "show index from " . addslashes($this->tablename) . " where Key_name = '" . addslashes($name) . "' ;";
		$result = $this->mysql->query($sql);

		if(!$result->num_rows()){
			
			$cols = "";
			foreach($columns as $column){
				if(strlen($cols)){
					$cols .= ", ";
				}
				if(is_string($column)){
					$cols .= "`" . addslashes($column) . "`";
				}else if(is_array($column)){
					$cols .= "`" . addslashes($column[0]) . "`(" . ((int)$column[1]) . ")";
				}
			}
			
			$sql = "ALTER TABLE `" . addslashes($this->tablename) . "` ADD INDEX `" . addslashes($name) . "` (" . $cols . ");";
			$this->mysql->query($sql);
		}
	}


    /**
     * will save the input json object contains
     * a value for the primary column or will
     * insert a new row
     * @throws DatabaseException
     */
	public function save($json_obj) : MySQLResult{
		$this->validateTableFor($json_obj);
		$primary = $this->primary;
		
		$primary_value = false;
		if(is_array($json_obj) && isset($json_obj[$primary])){
			$primary_value = $json_obj[$primary];
		}else if(is_object($json_obj) && isset($json_obj->$primary)){
			$primary_value = $json_obj->$primary;
		}
		
		if($primary_value){
			$res = $this->find(array($primary => $primary_value));
			if($res->num_rows()){
				// already exists with this primary key value, update it
				return $this->update($json_obj);
			}else{
				// doesn't exist yet, insert
				return $this->insert($json_obj);
			}
		}else{
			return $this->insert($json_obj);
		}
	}


    /**
     * returns a MysqlResult for a SELECT query
     * that tries to find all values in the table
     * that match the input json object
     * @throws DatabaseException
     */
    public function find(array $json_obj = array(), array $ops = null, array $orders = null) : MySQLResult{
        $this->validateTableFor($json_obj);
        $where = "";
        foreach ($json_obj as $key => $value) {
            if (is_array($value)) {
                $colname = $this->getColumnNameForKey($key);
                if (strlen($where)) {
                    $where .= " AND ";
                }
                $where .= "`" . $colname . "`";
                $op = ($ops && $ops[$key]) ? addslashes($ops[$key]) : "IN";
                $where .= " $op (";
                $idx = 0;
                foreach ($value as $val) {
                    $where .= ($idx ? "," : "") . "'" . addslashes($val) . "'";
                    $idx++;
                }
                $where .= ") ";
            } else if (is_object($value)) {
                /* 				echo "need to handle object subdata\n"; */
            } else {
                $colname = $this->getColumnNameForKey($key);
                if (strlen($where)) {
                    $where .= " AND ";
                }
                $where .= "`" . $colname . "`";
                if ($this->getMysqlTypeForValue($value) == "TEXT") {
                    $op = ($ops && $ops[$key]) ? addslashes($ops[$key]) : "LIKE";
                    $where .= " $op '" . addslashes($value) . "'";
                } else {
                    $op = ($ops && $ops[$key]) ? addslashes($ops[$key]) : "=";
                    $where .= " $op '" . addslashes($value) . "'";
                }
            }
        }
        $sql = "SELECT * FROM `" . addslashes($this->tablename);
        if ($where) {
            $sql .= "` WHERE " . $where;
        } else {
            $sql .= "`";
        }

        if (is_array($orders) && count($orders)) {
            $sql .= " ORDER BY ";

            foreach ($orders as $i => $order) {
                $sql .= addslashes($order);
                if ($i < count($orders) - 1) {
                    $sql .= ", ";
                }
            }
        }

        return $this->mysql->query($sql);
    }

    /**
     * finds the rows just like the find() method
     * and then deletes all of them
     * @throws DatabaseException
     */
	public function delete($json_obj) : MySQLResult{
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
		if(strlen($where)){
			$sql = "DELETE FROM `" . addslashes($this->tablename) . "` WHERE " . $where;			
			
			return $this->mysql->query($sql);
		}else{
            $sql = "SELECT 0 LIMIT 0";
            return $this->mysql->query($sql);
        }
	}

    /**
     * finds the rows just like the find() method
     * and then deletes all of them
     * @throws DatabaseException
     */
	public function truncate() : MySQLResult{
		$sql = "TRUNCATE `" . addslashes($this->tablename) . "`";
		return $this->mysql->query($sql);
	}

    /**
     * will update a row in the database for
     * the input object by comparing its value
     * for the primary column name
     * @throws DatabaseException
     */
	public function update($json_obj) : MySQLResult{
		$this->validateTableFor($json_obj);
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
				
				if(is_bool($value)){
					$value = (int)$value;
				}
				
				if(is_null($value)){
					$set .= " = NULL";
				}else{
					$set .= " = '" . addslashes($value) . "'";
				}
			}
		}
		if(strlen($set)){
			$sql = "UPDATE `" . addslashes($this->tablename) . "` SET "
				 . $set . " WHERE `" . $this->primary . "`='" . addslashes($primary_val) . "';";
			
			return $this->mysql->query($sql);
		}else{
            $sql = "SELECT 0 LIMIT 0";
            return $this->mysql->query($sql);
        }
	}

    /**
     * this method will attempt to add a new row
     * to the table with all of the values
     * of the input json object
     * @throws DatabaseException
     */
	public function insert($json_obj) : MySQLResult{
		$this->validateTableFor($json_obj);
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
				
				if(is_bool($value)){
					$value = (int)$value;
				}

				if(is_null($value)){
					$values .= "NULL";
				}else{
					$values .= "'" . addslashes($value) . "'";
				}
			}
		}
	
		if(strlen($fields)){
			$sql = "INSERT INTO `" . addslashes($this->tablename) . "` "
				 . "(" . $fields . ") VALUES (" . $values . ")";
			
			return $this->mysql->query($sql);
		}else{
            $sql = "SELECT 0 LIMIT 0";
            return $this->mysql->query($sql);
        }
	}

    /**
     * returns true if the input column name already exists
     * in the table, or false otherwise
     * @throws DatabaseException
     */
	protected function columnExistsInTableHuh($columnname) : bool {
		if(count($this->fields)){
			foreach($this->fields as $field){
				if($field["name"] == $columnname){
					return true;
				}
			}
			return false;
		}
		$sql = "SHOW COLUMNS FROM `" . addslashes($this->tablename) . "` LIKE '" . addslashes($columnname) . "'";
		$result = $this->mysql->query($sql);
		return $result->num_rows() > 0;
	}	

	

}

