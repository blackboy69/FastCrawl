<?
include_once "config.inc";

$ap=new Address_Parser();
foreach(db::query("SELECT * from netvu_yahoo",$returnAllRows=true) as $row)
{
	$parsed = $ap->parse($row['LOOKUP_RAW_ADDRESS']);
	@log::info("{$row['id']}
	LOOKUP_ADDRESS2 = {$parsed['ADDRESS2']}
	LOOKUP_ADDRESS  = {$parsed['ADDRESS']}
	LOOKUP_CITY		 = {$parsed['CITY']}
");
	
	R::exec("UPDATE LOW_PRIORITY netvu_yahoo set LOOKUP_ADDRESS2 = ?,LOOKUP_ADDRESS = ?,LOOKUP_CITY = ? where id = ?",array(@$parsed['ADDRESS2'] , $parsed['ADDRESS'] ,$parsed['CITY'],$row['id']));

}
