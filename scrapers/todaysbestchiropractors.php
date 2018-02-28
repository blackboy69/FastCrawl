<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class todaysbestchiropractors extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
//		$this->proxy = "localhost:8888";
		$this->maxRetries = 1;
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=2;
		$this->debug=false;
		
		//
/*		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			


		db::query("UPDATE raw_data set processing = 0 where type='todaysbestchiropractors' and parsed = 1 ");
	*/
#		db::query("	DROP TABLE todaysbestchiropractors"); 
		db::query("UPDATE raw_data set parsed = 0 where type='todaysbestchiropractors' and parsed = 1 ");
		db::query("DROP TABLE $type");	
	//	$this->loadUrlsByLocation("http://www.todaysbestchiropractors.com/lasik-doctors?zip=$ZIP&dist=100",500);
		$states = array ('Alabama','Alaska','Alberta','Arizona','Arkansas','British Columbia','California','Colorado','Connecticut','District of Columbia','Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Manitoba','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Brunswick','New Hampshire','New Jersey','New Mexico','New York','Newfoundland & Labrador','North Carolina','North Dakota','Northwest Territories','Nova Scotia','Nunavut','Ohio','Oklahoma','Ontario','Oregon','Pennsylvania','Prince Edward Island','Puerto Rico','Quebec','Rhode Island','Saskatchewan','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming','Yukon Territory');
		
		foreach ($states as $state)
		{
			$urls[] = "http://www.todaysbestchiropractors.com/search.php?state=".strtolower(urlencode($state));
		}
		$this->loadUrlsByArray($urls);

	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$xListing = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();

		
		foreach($xListing->query("//div[@class='premium' or @class='normal'] ") as $nodeListing)
		{
			$x = new XPath($nodeListing);	

			$data = array();
			foreach ($x->query("//h4") as $node)
			{
				list($data['NAME'], $junk) = explode(",", $node->textContent);
			}
			

			foreach ($x->query("//p") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));

				if (isset($data['ZIP']) && isset($data['CITY']) && isset($data['STATE'])  && isset($data['ADDRESS']))
					break;
			}

			foreach ($x->query("//p") as $node)
			{
				$data = array_merge($data,$pp->parse($node->c14n()));
			}		

			$data = array_merge($data,$ep->parse($nodeListing->c14n()));


			foreach ($x->query("//a") as $node)
			{
				$data['WEB_SITE'] =  $node->getAttribute("href");
			}
		
			if (!empty($data['NAME']) )
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('NAME','PHONE','ZIP'));	
			}
		}
	}
}

$r= new todaysbestchiropractors();
$r->parseCommandLine();

