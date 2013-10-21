<?
/**
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class Timer{

	protected $start;
	protected $stop;

	function __construct(){
		$this->start = false;
		$this->stop = false;
	}

	function start(){
		 $this->start = (float)microtime(true);
		 $this->stop = false;
	}

	function stop(){
		 $this->stop = (float)microtime(true);
	}

	function read(){
		if(is_numeric($this->stop) &&
		   is_numeric($this->start) &&
		   ($this->stop > $this->start)){
			return ($this->stop - $this->start);
		}else
		if(is_numeric($this->start)){
			return (microtime(true) - $this->start);
		}else{
			return 0;
		}
	}
}

?>