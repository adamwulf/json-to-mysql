<?
/**
 * Licensed under Creative Commons 3.0 Attribution
 * Copyright Adam Wulf 2013
 */

define("ROOT", dirname(__FILE__) . "/");
define("DATABASE_HOST", "localhost");
define("DATABASE_NAME", "internet_metrics");
define("DATABASE_USER", "internet_metrics");
define("DATABASE_PASS", "WFw7ZUowHrq3Vz");

include("include.classloader.php");

$classLoader->addToClasspath(ROOT);


$mysql = new MySQLConn(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASS);

$db = new JSONtoMYSQL($mysql);

// create some json
$obj = json_decode('{"id":4,"asdf" : "asfd"}');

// save it to a table
$db->save($obj, "brandnewtable");


?>