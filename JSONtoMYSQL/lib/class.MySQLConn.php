<?php
/**
 * a class for managing a MySQL connection
 * and caching certain queries
 *
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class MySQLConn{

	private $host;
	private $database;
	private $user;
	private $pass;
	private $timer;

	/**
	 * optional logger
	 */
	private $logger;

	// the link, or false if none
	private $_mysqli_link;
	private $_query_count;


	public function __construct($h, $db, $u, $p, $log = null){
		$this->host = $h;
		$this->database = $db;
		$this->user = $u;
		$this->pass = $p;
		$this->_query_count = 0;
		$this->_mysqli_link = false;
		$this->_query_cache = new HashTable();
		if($log !== null && !($log instanceof ALogger)){
			throw new IllegalArgumentException("optional argument to " . __METHOD__ . " must be an ALogger");
		}
		$this->logger = $log;
	}

	// queries mysql and caches the result if appropriate
	function query($sql, $verbose=false){
		$sql = trim($sql);
		if($this->_mysqli_link === false){
                    $this->_mysqli_link = mysqli_connect($this->host, $this->user, $this->pass, $this->database);
                    mysqli_set_charset($this->_mysqli_link, "utf8");
		}
		if($this->_mysqli_link === false){
			throw new Exception("could not connect to MySQL");
		};

		if($this->_query_cache->get($sql)){
			if($verbose)echo "found in cache<br/>";
			$result = $this->_query_cache->get($sql);
			if(mysqli_num_rows($result)){
				if($verbose) echo ": seeking to 0";
				mysqli_data_seek($result, 0);
			}
			$ret = new MySQLResult($this->_mysqli_link, $result);
			if($verbose) echo "<br/>";
		}else{
			if($verbose) echo "not in cache";
			$this->_query_count++;
			/**
			 * this following line should be run once per connection to mysql
			 *
			 * i'm running it before every query. I can probably optimize this
			 * to run once per connection, but I need to do some thorough testing...
			 *
			 * http://dev.mysql.com/doc/refman/5.6/en/charset-connection.html
			 */
			if(is_object($this->logger)){
				$this->logger->log($this, ALogger::$LOW, $sql);
			}
			
                        mysqli_set_charset($this->_mysqli_link, "utf8");
			$timer = new Timer();
			$timer->start();
			$result = mysqli_query($this->_mysqli_link, $sql);
			$ret = new MySQLResult($this->_mysqli_link, $result);
			$timer->stop();
			$time = $timer->read();
			
			/**
			 * the query is too long! oh noes!
			 */
			if($time > .1){
				/**
				 * save the query to the DB, so I can look at it later
				 */
				if(is_object($this->logger)){
					$this->logger->longQuery($time, $sql);
				}
			}
			
			if(mysqli_error($this->_mysqli_link)){
				if($verbose) echo "mysqli_error: " . mysqli_error($this->_mysqli_link) . "<br>";
				throw new Exception(mysqli_error($this->_mysqli_link));
			}
			if(strpos($sql, "SELECT") === 0){
				if($verbose) echo ": select: $sql<br><br>";
				$this->_query_cache->put($sql, $result);
			}else{
				if($verbose) echo ": not select: $sql<br>";
				if($verbose) echo "clearing cache<br>";
				$this->_query_cache->reset();
			}

		}
		return $ret;
	}
	
	function reset(){
		$this->_query_count = 0;
		$this->_query_cache->reset();
	}

	function getQueryCount(){
		return $this->_query_count;
	}
	
	function close(){
		if(!is_bool($this->_mysqli_link)){
			return @mysqli_close($this->_mysqli_link);
		}else{
			return false;
		}
	}

}

?>
