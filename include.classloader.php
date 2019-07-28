<?php /** @noinspection PhpIncludeInspection */

/**
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */
class ClassLoader{
	protected $classpath;

	public function __construct(){
		$this->classpath = array();
	}

    /**
     * @param $dir
     * @throws Exception
     */
    public function addToClasspath($dir) : void{
		if(is_dir($dir)){
			$this->classpath[] = $dir;
		}else{
			throw new Exception("cannot find directory: $dir");
		}
	}

    /**
     * @param $classname
     * @return bool
     */
    public function load($classname) : bool{
		$ok = false;
        foreach ($this->classpath as $path) {
/* 			echo "load recur \"" . $path . "\";//<br>\n"; */
            $ok = $ok || $this->load_recursive($path, $classname);
        }
        return $ok;
	}

	protected function load_recursive($classpath, $classname) : bool{
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
						}else
						if($file == "abstract.$classname.php"){
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
  }

  class ClassLoaderToString extends ClassLoader{

	protected function load_recursive($classpath, $classname) : bool{
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

	protected function printClass($classpath, $file) : void{
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
		$classLoader->load($classname);
//		$str .= ":" . $ok;
//		if(is_object($control) && !is_int(stripos($classname, "mysql"))){
//			$control->getModel()->getLogger()->log($control->getModel(), ALogger::$HIGH, $str);
//		}
	}catch(Exception $e){
        /** @noinspection ForgottenDebugOutputInspection */
        error_log($e->getMessage());
//		$model->getLogger()->log($model, ALogger::$HIGH, print_r($e, true));
	}
  }

spl_autoload_register('milestone_autoload');

	$classLoader = new ClassLoader();
