<?

include_once("address_parser.php");
include_once("scrape/db.inc");
db::init2("demandforce");


//stack overflow generic tests
$stackoverflow[] = "A. P. Croll & Son 2299 Lewes-Georgetown Hwy, Georgetown, DE 19947";
$stackoverflow[] = "11522 Shawnee Road, Greenwood DE 19950";
$stackoverflow[] = "144 Kings Highway, S.W. Dover, DE 19901";
$stackoverflow[] = "Intergrated Const. Services 2 Penns Way Suite 405 New Castle, DE 19720";
$stackoverflow[] = "Humes Realty 33 Bridle Ridge Court, Lewes, DE 19958";
$stackoverflow[] = "Nichols Excavation 2742 Pulaski Hwy Newark, DE 19711";
$stackoverflow[] = "2284 Bryn Zion Road, Smyrna, DE 19904";
$stackoverflow[] = "VEI Dover Crossroads, LLC 1500 Serpentine Road, Suite 100 Baltimore MD 21";
$stackoverflow[] = "580 North Dupont Highway Dover, DE 19901";

$stackoverflow[] = "P.O. Box 778 Dover, DE 19903-1234";
$stackoverflow[] = "11578 Allisonville Rd Indianapolis IN 46038";
$stackoverflow[] = "11522 Shawnee rd. Greenwood DE 19950";
$stackoverflow[] = "1855 E Contra Costa Ave Pleasant Hill,CA";

$stackoverflow[]= "409 NE Greenwood, Suite 101, Bend, OR 97701";

$stackoverflow[]= "409 NE Greenwood, 23rd floor suite 3, San franceisco, OR 97701";
$stackoverflow[]= " 2370 York Rd Ste A1,Jamison, PA 18929";
$stackoverflow[]= "301 Oxford Valley Rd Ste 801A Yardley, PA 19067-7714 ";
 $stackoverflow[]= "215 W 88th St Apt 7F New York, NY 10024-2354  ";
$stackoverflow[]="  P.O. Box 2240,Petaluma, California 94953,United States";
$stackoverflow[]=" 4900 S. College Avenue, # 120, Fort Collins, CO 80525 ";

$stackoverflow[]=" 4525 149th Ct.    Apple Valley  Minnesota  55124   United States"; // FAILS
$stackoverflow[]="1260 South Spectrum Blvd.    Chandler  Arizona  85286   United States";
$stackoverflow[]="452 Torbay Road St John’s, Newfoundland S6J 1L4";

//$stackoverflow=array();
$stackoverflow[]="2600 Central Ave, #B, Union City, CA , 94587";
$stackoverflow[]="14039 Sherman Way, #205 ,Van Nuys, CA, 91405";
$stackoverflow[]="61 Van Brunt MANOR, Rd ,Setauket, NY, 11720";
$stackoverflow[]="210 W Maumee ST, ,Angola, IN, 46703-1425";
$stackoverflow[]="118 S HIGHWAY, 25 ,Leoti, KS, 67861";

$stackoverflow[]="1812 Eastchester DR, ,High Point, NC, 27261";

test($stackoverflow,true);

/*
test(db::oneCol("SELECT Full_Address from demandforce.chirohub"));
test(db::oneCol("SELECT Full_Address from demandforce.mechanicnet"));
test(db::oneCol("SELECT full_location from jordan.napaautopro"));
test(db::oneCol("SELECT Full_Address from jordan.nysdental"));

*/
function test($addresses,$verbose=false)
{
	$ap = new Address_Parser();
	$total = sizeof($addresses);
	$failed = 0;
		
	foreach($addresses as $address)
	{
		$fail = false;
		$res = $ap->parse($address);
		//log::info($res);
		if (! isset($res['ZIP']))
		{
			// the zip code actually exists in the string.
			if ($ap->findZip($address) )
			{
				$fail = true;
			}
		}
		if ( !isset($res['CITY']) )
		{
			$fail = true;
		}

		if ( !isset($res['STATE']) )
		{
			$fail = true;
		}

		if ($fail)
		{
			log::info("==== FAILED $address");
			//log::info($res);
			$failed++;
		}

		if($verbose)
		{
			log::info($address);
			log::info($res);
		}
	}
	log::info("$failed of $total addresses failed");
}