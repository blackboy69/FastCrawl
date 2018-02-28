<?

include_once "config.inc";

$ap = new Address_Parser();
$op = new Operating_Hours_Parser();
$pp = new Phone_Parser();
$kvp = new KeyValue_Parser();
$np = new Name_Parser();


print_r($ap->parse(file_get_contents("http://www.levittownvet.com/")));