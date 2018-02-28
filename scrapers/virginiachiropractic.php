<?
include_once "config.inc";

class virginiachiropractic extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		#db::query("DELETE FROM load_queue where type='$type' ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		#db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");
		db::query("DELETE FROM $type");
		
		

		
		$this->threads=1;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
//		unlink ($this->cookie_file);
	//	$this->useCookies=true;
		//$this->login();
		//$this->loadUrl("https://www.virginiachiropractic.org/member/findadentist_results.aspx?fragmentid=166&lastname=&city=&withinmiles=0&zipcode=01230&practicetype=AllSpecialties&foreignlanguage=AllLanguages");

		$cities = 'Abingdon', 'Afton', 'Alexandria', 'Altamonte', 'Altoona', 'Amherst', 'Ankeny', 'Annandale', 'Arlington', 'Arvada', 'Ashburn', 'Ashland', 'Baltimore', 'Beckley', 'Berryville', 'Big Stone Gap', 'Blacksburg', 'Boydton', 'Boynton Beach', 'Bristow', 'Broadway', 'Buchanan', 'Burke', 'Cape Charles', 'Carrollton', 'Cedar Rapids', 'Centennial', 'Centerville', 'Centreville', 'Channahen', 'Charlottesville', 'Chesapeake', 'Chester', 'Chesterfield', 'Christiansburg', 'Clifton', 'Coldwater', 'Colonial Beach', 'Colonial Heights', 'Cross Junction', 'Crozet', 'Culpeper', 'Daniels', 'Danville', 'Deer Park', 'Dobson', 'Dugspur', 'Dunn Loring', 'Earlysville', 'East Rutherford', 'Edinburg', 'Elkton', 'Evington', 'Fairfax', 'Fairfax Station', 'Falls Church', 'Forest', 'Franklin', 'Frederick', 'Fredericksburg', 'Ft Washington', 'Gainesville', 'Gaithersburg', 'Glen Allen', 'Goode', 'Grand Haven', 'Great Falls', 'Gresham', 'Hacksneck', 'Halifax', 'Hamilton', 'Harrisonburg', 'Haymarket', 'Henrico', 'Henry', 'Herndon', 'Hillsville', 'Keezletown', 'Keswick', 'Keysville', 'King George', 'Lanexa', 'Lebanon', 'LeClaire', 'Leesburg', 'Locust Grove', 'Lorton', 'Lutherville', 'Lynchburg', 'Lyndhurst', 'Machipougo', 'Manassas', 'Martinsville', 'McLean', 'Mechanicsville', 'Midlothian', 'Montpelier', 'Nellysford', 'New Canton', 'Newport', 'Newport News', 'Nokesville', 'Norfolk', 'North Tazewell', 'Oak Hill', 'Oakton', 'Palmyra', 'Parnell', 'Pelham', 'Penhook', 'Petersburg', 'Port Orange', 'Port Republic', 'Portsmouth', 'Pounding Mill', 'Powhatan', 'Purcellville', 'Reston', 'Richmond', 'Ridgeway', 'Roanoke', 'Roanoke Rapids', 'Rockbridge Baths', 'Rockville', 'Romeo', 'Romulus', 'Round Hill', 'Rustburg', 'Salem', 'Saluda', 'San Francisco', 'Seneca Falls', 'Singing Glen', 'South Hill', 'Spotsylvania', 'Spout Springs', 'Springfield', 'Stafford', 'Stanardsville', 'Staunton', 'Stephens City', 'Sterling', 'Stuarts Draft', 'Suffolk', 'Tarboro', 'Tazewell', 'Tazwell', 'Troutville', 'Union Hall', 'Vienna', 'Virginia Beach', 'Warrenton', 'Washington', 'Waynesboro', 'Whitewood', 'Williamsburg', 'Winchester', 'Woodbridge', 'Woodstock', 'Wytheville';

		foreach($city in $cities)
		{

		// $this->loadUrl("http://www.virginiachiropractic.com/index.php?option=com_mtree&task=listcats&cat_id=67&Itemid=103");
		#$this->loadUrl("http://www.virginiachiropractic.com/index.php?option=com_mtree&task=listcats&cat_id=7554&Itemid=103");
		}

		$this->loadUrl("http://www.virginiachiropractic.org/displaycommon.cfm?an=1&subarticlenbr=146&search_LastName=&searchmatch_LastName=Starts&search_CompanyName=&searchmatch_CompanyName=Starts&search_wCity=Alexandria&searchmatch_wCity=Starts&search_wZip=&searchmatch_wZip=Starts&search_WorkPhone=&searchmatch_WorkPhone=Exact&searchfield_type=and&newpage=No&search=search&searchassnnbr=10775&searchtype=Find+A+Doc&displaytype=Vendor");
		$this->queuedPost();
   }
	
	static function i
	{
		return self::getInstance();
	}

	static function parse($url,$html)
	{

		
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$addressParser = new Address_Parser();

		$found  = false;
		
		
			
		foreach ($x->query("//div[@id='results']//a") as $listing)
		{
			$href = "http://www.virginiachiropractic.org/".$listing->getAttribute("href");
			self::i()->loadUrl($href)
				log::info ("Loading $href");
		}
			/*
			log::info($listing->textContent);
			//don't load pages with 0 listings
			if (! strstr($listing->textContent,"(0)"))
			{
				$dom2 = new DOMDocument();
				@$dom2->loadHTML($listing->c14n());
				$x2 = new DOMXPath($dom2);	
				foreach ($x2->query("//a") as $node)
				{
					log::info("Loading $listing->textContent");
					self::getInstance()->loadUrl("http://www.virginiachiropractic.com/".$node->getAttribute("href"));
					$found = true;
				}
			}
		}

		if (!$found)
		{

			foreach ($x->query("//div[@class='listing-summary']") as $listing)
			{
				$data=array();
				$dom2 = new DOMDocument();
				@$dom2->loadHTML($listing->c14n());
				$x2 = new DOMXPath($dom2);	
				foreach ($x2->query("//h3") as $node)
				{
					$data['Name'] = $node->textContent;
				}
				foreach ($x2->query("//div[@class='address']") as $node)
				{
					$data = array_merge($data, $addressParser->parse($node->textContent));
				}
				foreach ($x2->query("//div[@class='fieldRow']") as $node)
				{
					$dom3 = new DOMDocument();
					@$dom3->loadHTML($node->c14n());
					$x3 = new DOMXPath($dom3);	

					
					foreach ($x3->query("//span[@class='caption']") as $node3)
					{
						$key = $node3->textContent;
					}
					foreach ($x3->query("//span[@class='output']") as $node3)
					{

						$value = $node3->textContent;
					}

					if ($key == "E-mail")
					{
						if (preg_match("/':'\+'(.+)\"/",$value,$matches))
						{
							$value = urldecode($matches[1]);
						}
					}
					$data[$key] = $value;
				}


				log::info($data);								
				db::store($type,$data,array('Name','Telephone'),false);
			}
		
		*/	
		}
		
	}


	static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/","",$str);
	}
	
}
$r = new virginiachiropractic();
$r->runLoader();
$r->parseData();
$r->generateCSV();
