<?php
/**
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class HashTable {
	protected $table;

	function __construct(){
		$this->table = array();
	}
  
	function put($key, $obj): void
    {
		$this->table[$key] = $obj;
	}

	function get($key){
		if(isset($this->table[$key])){
			return $this->table[$key];
		}else{
			return false;
		}
	}

	function clear($key): void
    {
		if(isset($this->table[$key])){
			unset($this->table[$key]);
		}
	}

	function reset(): void
    {
		$this->table = array();
	}


	function enum(): array
    {
		$ret = [];
		foreach($this->table as $key => $item){
			$ret[] = $item;
		}
		return $ret;
	}

}

