<?
include_once "config.inc";
include_once "HtmlParser.php";

class bcdental extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		# db::query("DELETE FROM load_queue where type='$type' ");
		# db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		# db::query("DELETE FROM raw_data where type='$type' ");
		#db::query("DELETE FROM $type");

		
		$this->threads=1;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		$this->proxy = "localhost:8888";
		$this->useCookies = false;

		$url = "http://www.bcdental.org/Find_a_Dentist/DentistSearch.aspx";
		$html = $this->GET($url);

		$parser = new HtmlParser($html);

		
		// this isn't implemented yet :)
		// $data = $parser->loadDefaults("//form[@id='aspnetForm']");
		

		$data = $parser->loadViewState();
		
		$data['ctl00$SearchBox2$searchTxt'] ='' ;
		$data['ctl00$mainContent$drpCity'] = 0;
		$data['ctl00$mainContent$txtPostalCode'] = ''; // %ZIP%;
		$data['ctl00$mainContent$drpSpecialty'] = '';
		$data['ctl00$mainContent$drpLanguage'] = 0;
		$data['ctl00$mainContent$drpSedation'] = 0;
		$data['ctl00$mainContent$btnSearch'] = 'Search';

		
		// do all parsing not using teh queue because post size is 16k. we can only index up to 1k in the db. $this means the post looks like it isn't unique and causes problems in the queued post. Need to update queued post to use sha1 hash as unique key, and convert url to a text column
		$types = array('GP','dph','endo','oral_max','oral_med','ompath','oral_path','oral_rad','ortho','pediatric','perio','prostho');
		foreach ($this->getCities() as $city)
		{
			if ($city < 79) continue;
			$data['ctl00$mainContent$drpCity'] = $city;

			foreach ($types as $type)
			{
				$data['ctl00$mainContent$drpSpecialty'] = $type;
				log::info("$city: $type");
				log::info("Posting ...");

				$toPost = "$url?".$this->buildQuery($data,false);
				$this->setReferer($toPost,"http://www.bcdental.org/Find_a_Dentist/DentistSearch.aspx");				
				$response = $this->Post($toPost);
				log::info("Got ". strlen($response). " Bytes");
				
				$this->simpleParse($url, $response);
			}
		}
   }	

	private function simpleParse($url,$html)
	{
		$data=array();
		$type = get_class();			
		$x = new HtmlParser($html);	
		
		foreach ($x->query("//table[@width='90%']") as $listing)
		{
			$x2 = new HtmlParser($listing->c14n());	
			$data = array();
			
			foreach($x2->query("//td[1]/a") as $node)
			{//map
				$href = $node->getAttribute("href");
				
				$data['address'] = self::urlVar($href,'address');
				$data['city'] = self::urlVar($href,'city');
				$data['state'] = self::urlVar($href,'state');
				$data['country'] = self::urlVar($href,'country');				
			}

			foreach($x2->query("//td[2]") as $node)
			{//name/address
				$data['name'] = strip_tags(preg_replace("/<br>.+/","", $node->c14n() ));

				$matches = array();
				preg_match("/([A-Z][0-9][A-Z] [0-9][A-Z][0-9])<\/td>/",$node->c14n(),$matches);
				$data['zip'] = $matches[1];

			}
			foreach($x2->query("//td[3]") as $node)
			{//phone

				
				$data['phone'] = trim(strip_tags(preg_replace("/<br>.+/","", $node->c14n() )));
				
				$x3 = new HtmlParser($node->c14n());
				foreach ($x3->query("//a") as $a)
				{
					$href = $a->getAttribute("href");

					if ($href != "http://")
					{
						$data['website'] = $href;
					}
				}
			}
			
			log::info($data);
         db::replaceInto($type,$data);
		}
	}

		
	function getCities()
	{
		$r =  array  (1, 1100, 3, 4, 7, 8, 11, 12, 952, 1400, 728, 18, 602, 19, 23, 647, 1673, 1130, 1035, 27, 998, 1811, 33, 1648, 41, 1277, 1287, 44, 1107, 54, 55, 56, 824, 61, 1224, 1724, 985, 67, 68, 1359, 75, 1218, 599, 1311, 79, 598, 80, 81, 894, 82, 580, 84, 85, 87, 1563, 89, 812, 92, 842, 96, 1532, 97, 722, 683, 1595, 100, 101, 1347, 108, 926, 885, 110, 1214, 114, 115, 116, 975, 865, 649, 928, 120, 767, 121, 122, 127, 637, 129, 130, 132, 133, 914, 1406, 140, 1042, 141, 1608, 1649, 1975, 145, 1549, 1559, 146, 1272, 1030, 693, 155, 1329, 1203, 157, 1027, 1215, 164, 165, 167, 169, 596, 1135, 1531, 159, 1151, 162, 171, 1511, 173, 174, 1488, 175, 1869, 176, 764, 178, 1217, 1575, 184, 185, 193, 1710, 1366, 1663, 188, 794, 192, 834, 197, 202, 204, 1789, 206, 207, 638, 660, 634, 867, 216, 592, 1193, 217, 902, 219, 225, 1082, 923, 231, 232, 234, 236, 239, 241, 1522, 244, 245, 1299, 1687, 1373, 703, 251, 252, 1237, 248, 1190, 1472, 1074, 255, 257, 259, 1659, 262, 1058, 266, 640, 268, 269, 1823, 271, 275, 1336, 281, 283, 284, 285, 286, 287, 1615, 1140, 289, 635, 1580, 292, 293, 294, 295, 296, 956, 297, 1749, 300, 301, 1610, 1529, 600, 304, 305, 308, 1952, 314, 1289, 1318, 317, 628, 322, 323, 324, 326, 870, 330, 332, 335, 1565, 340, 951, 351, 1298, 355, 359, 362, 1054, 366, 888, 368, 369, 1195, 699, 370, 374, 376, 707, 706, 1022, 381, 1380, 709, 711, 712, 714, 384, 385, 1487, 387, 389, 388, 391, 393, 1611, 394, 1036, 1390, 395, 396, 821, 825, 404, 405, 1364, 409, 411, 1046, 415, 416, 418, 1083, 646, 419, 424, 423, 1055, 1906, 431, 436, 1618, 726, 439, 1377, 443, 1677, 445, 1887, 450, 451, 1165, 776, 452, 1558, 1421, 455, 996, 456, 457, 459, 698, 1259, 966, 465, 1637, 470, 483, 971, 486, 1379, 1192, 488, 1841, 494, 1653, 780, 1945, 1204, 1571, 498, 499, 927, 1896, 1880, 507, 697, 822, 509, 513, 515, 518, 519, 522, 525, 526, 527, 944, 528, 530, 532, 537, 1544, 1239, 541, 1109, 542, 546, 1842, 547, 550, 551, 1132, 1001, 1234, 555, 556, 560, 561, 564, 1168, 566, 895, 569, 1788, 570, 957, 572, 760, 574);
		sort($r);

		return $r;

	}
}
$r = new bcdental();
$r->runLoader();
$r->parseData();
$r->generateCSV();
