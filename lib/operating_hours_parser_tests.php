<?

include_once("operating_hours_parser.php");

include_once("scrape/db.inc");
db::init2("demandforce");

$testStrings[] = "Monday through Friday 8-6 Saturday 8-3";
$testStrings[] = "M T W F 7:30-5:30. Thur 7:30 - 7 Saturday: 8 - 3. Sunday closed.";
$testStrings[] = "TUES - FRI: 8:00 AM - 12:00 PM 2:00 PM - 5:00 PM";
$testStrings[] = "TUES:8:30AM-5:00PMTHURS:8:30AM-5:00PMSAT:8:00AM-1:00PM";
$testStrings[] = "MON: 7:00  AM  - 4:00  PM TUES  -  WED: 7:00  AM  - 5:00  PM THURS: 7:00  AM  - 4:00  PM";
$testStrings[]= "Mon - Thurs: 8:00 am-5:00 pm";
$testStrings[]= "Sun - Thurs: 8:00 am-5:00 pm Sat 6-8 ";
$testStrings[]= "Tu - Thurs: 8:00 am-5:00 pm Sat and Sun 6-8 ";
$testStrings[]= "Fri - Sun 8-5 Wed and Thu 6a-12p, 6p-8pm ";
$testStrings[]= "Monday through Friday: 7:30-5:30. Saturday:7:30-2";
$testStrings[]= "Monday through Friday: 7:30-5:30. Saturday:Closed";
$testStrings[]= "NOW OPEN<br /><br />M - F: 7:30am - 6:00 pm<br />SAT:  7:30 am - 5:00 pm<br />SUN:  CLOSED";

$hours[] = "Mon 8:00AM - 8:00PM";
$hours[] = "Tue 8:00AM - 8:00PM";
$hours[] = "Wed 8:00AM - 8:00PM";
$hours[] = "Thur 8:00AM - 8:00PM  ";
$hours[] = "Fri 8:00AM - 8:00PM";
$hours[] = "Sat 8:00AM - 6:00PM ";
$hours[] = "Sun 10:00AM - 6:00PM";

$testStrings[]=  $hours;
# $testStrings= db::oneCol("SELECT Hours from jordan.facebook_dentists");


$oh = new Operating_Hours_Parser();
foreach($testStrings as $str)
{
	if (!empty($str))
	{
		
		$res = $oh->parse ($str);

		

			print_r($res);

	}
}


