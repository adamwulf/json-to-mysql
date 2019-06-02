json-to-mysql
=============

Easily take any JSON object and create+insert it into a mysql table built from its structure. Also search, update, index, and validate tables with JSON.

```

include("config.php");
include("include.classloader.php");

$classLoader->addToClasspath(ROOT);


$mysql = new MySQLConn(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASS);

$db = new JSONtoMYSQL($mysql);

// create some json
$obj = json_decode('{"id":4,"asdf" : "asfd"}');

// save it to a table
$db->save($obj, "brandnewtable");

$obj = $db->table("brandnewtable")->find(["id" => 4]);

print_r($obj);

```