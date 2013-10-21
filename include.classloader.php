<?
/**
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class ClassLoader{
	protected $classpath;

	public function __construct(){
		$this->classpath = array();
	}

	public function addToClasspath($dir){
		if(is_dir($dir)){
			$this->classpath[] = $dir;
		}else{
			throw new Exception("cannot find directory: $dir");
		}
	}
	
	public function load($classname){
		$ok = false;
		for($i=0;$i<count($this->classpath);$i++){
			$path = $this->classpath[$i];
/* 			echo "load recur \"" . $path . "\";//<br>\n"; */
			$ok = $ok || $this->load_recursive($path, $classname);
		}
		return $ok;
	}

	protected function load_recursive($classpath, $classname){
		$theList = array();
		$ret = false;
		if ($handle = opendir($classpath)) {
			while (false != ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if(is_dir($classpath . $file)){
						$ret = $ret || $this->load_recursive($classpath . $file . "/", $classname);
					}else{
						if($file == "class.$classname.php"){
							include_once $classpath . $file;
							$ret = true;
/* 							echo "include_once \"" . $classpath . $file . "\";//<br>\n"; */
						}else
						if($file == "class.Boolean.$classname.php"){
							include_once $classpath . $file;
							$ret = true;
/* 							echo "include_once \"" . $classpath . $file . "\";//<br>\n"; */
						}else
						if($file == "interface.$classname.php"){
							include_once $classpath . $file;
							$ret = true;
/* 							echo "include_once \"" . $classpath . $file . "\";//<br>\n"; */
						}
					}
				}
			}
		closedir($handle);
		unset($handle);
		}
		return $ret;
	}
	
	public function loadTestFiles(GroupTest $g){
		foreach($this->classpath as $c){
			$this->loadTestFilesHelper($g, $c);
		}
	}
	
	protected function loadTestFilesHelper(GroupTest $g, $classpath){
		$theList = array();
		if ($handle = opendir($classpath)) {
			while (false != ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if(is_dir($classpath . $file)){
						$this->loadTestFilesHelper($g, $classpath . $file . "/");
					}else{
						if(strpos($file, "test.class.") === 0 &&
						strpos($file, ".php") == strlen($file)-4){
							$g->addTestFile($classpath . $file);
						}
					}
				}
			}
		closedir($handle);
		unset($handle);
		}
		
	}
  }

  class ClassLoaderToString extends ClassLoader{

	public function __construct(){
		parent::__construct();
	}

	protected function load_recursive($classpath, $classname){
		$theList = array();
		$ret = false;
		if ($handle = opendir($classpath)) {
			while (false != ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if(is_dir($classpath . $file)){
						$this->load_recursive($classpath . $file . "/", $classname);
					}else{
						if($file == "class.$classname.php"){
							include_once $classpath . $file;
							$this->printClass($classpath, $file);
							$ret = true;
						}else
						if($file == "class.Boolean.$classname.php"){
							include_once $classpath . $file;
							$this->printClass($classpath, $file);
							$ret = true;
						}else
						if($file == "interface.$classname.php"){
							include_once $classpath . $file;
							$this->printClass($classpath, $file);
							$ret = true;
						}
					}
			}
		}
		closedir($handle);
		unset($handle);
		}
		return $ret;
	}

	protected function printClass($classpath, $file){
		if(strpos($classpath, ROOT) === 0){
			$classpath = substr($classpath, strlen(ROOT));
			echo "include_once(ROOT . \"" . $classpath . $file . "\");\n";
		}else{
			echo "include_once(\"" . $classpath . $file . "\");\n";
		}
	}
  }  
  
  
  function milestone_autoload($classname){
  	global $classLoader;
//	global $control;
//	$str = "classname: ";
//	$str .= $classname;
//	$str .= "\n";
//	if(is_object($control) && !is_int(stripos($classname, "mysql"))){
//		$control->getModel()->getLogger()->log($control->getModel(), ALogger::$HIGH, $str);
//	}
	try{
		$ok = $classLoader->load($classname);
//		$str .= ":" . $ok;
//		if(is_object($control) && !is_int(stripos($classname, "mysql"))){
//			$control->getModel()->getLogger()->log($control->getModel(), ALogger::$HIGH, $str);
//		}
	}catch(Exception $e){
		$model->getLogger()->log($model, ALogger::$HIGH, print_r($e, true));
	}
  }

spl_autoload_register('milestone_autoload');

	$classLoader = new ClassLoader();
?>