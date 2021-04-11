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
$obj = json_decode('{"id":4,"name" : "asfd"}');

// save it to a table
$db->save($obj, "brandnewtable");

// SELECT * from brandnewtable WHERE id = 4

$obj = $db->table("brandnewtable")->find(["id" => 4]);

print_r($obj);

// SELECT * FROM brandnewtable WHERE id > 4 ORDER BY name DESC

$obj = $db->table("brandnewtable")->find(["id" => 4], ["id" => ">"], ["name DESC"]);

print_r($obj);

// SELECT * FROM brandnewtable WHERE id IN (4, 5, 6, 7)

$obj = $db->table("brandnewtable")->find(["id" => [4, 5, 6, 7]]);

print_r($obj);


```

## Support the project

Has json-to-mysql saved you some time? Become a [Github Sponsor](https://github.com/sponsors/adamwulf) and buy me a coffee ☕️ 😄

