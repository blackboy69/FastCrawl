<?
include_once "config.inc";
error_reporting(E_ALL);
$ap = new Address_Parser();
$oh = new Operating_Hours_Parser(); 


$str = '<font color="000000" face="Verdana">Insight Optical<br></br><br></br>Derek Feifke, O.D.<br></br>Audra  Rowse, O.D.<br></br>Caroline Blackie, O.D.<br></br><br></br>50 Mall Rd.<br></br>Suite 114<br></br>Burlington, MA 01803<br></br><br></br>781-229-2020<br></br><br></br><br></br></font>';


$sections = explode("<br></br><br></br>",$str);



$data['Name'] = strip_tags($sections[0]);

$data = array_merge($data,$ap->parse( $sections[2] ));
$data['Phone'] = $sections[3];

$doctors = explode("<br></br>",$sections[1]);
		
for($i = 0;$i<sizeof($doctors);$i++)
{
	$data["Doctor ".($i+1)] = $doctors[$i];
}

for($i=4;$i<sizeof($sections);$i++)
{
	$data["Extra ".($i-3)] = strip_tags($sections[$i]);
}

log::info($data);