<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class repairpal extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		//$this->maxRetries = 100;
		//$this->timeout = 15;
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=4;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)		 
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='repairpal' and parsed = 1   ");
		*/
		// cananda top 100 cities by population
		//		db::query("UPDATE raw_data set parsed = 0 ");
	//	db::query("DELETE FROM load_queue where type='$type'");
//		
		$this->noProxy=true;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		



		$makes = array('acura' , 'audi' , 'bmw' , 'buick' , 'cadillac' , 'chevrolet' , 'chrysler' , 'dodge' , 'fiat' , 'ford' , 'geo' , 'gmc' , 'honda' , 'hummer' , 'hyundai' , 'infiniti' , 'isuzu' , 'jaguar' , 'jeep' , 'kia' , 'land+rover' , 'lexus' , 'lincoln' , 'mazda' , 'mercedes-benz' , 'mercury' , 'mini' , 'mitsubishi' , 'nissan' , 'oldsmobile' , 'plymouth' , 'pontiac' , 'porsche' , 'ram' , 'saab' , 'saturn' , 'scion' , 'smart' , 'subaru' , 'suzuki' , 'toyota' , 'volkswagen' , 'volvo');
		// load these first.
		$webRequests= array();
		foreach($makes as $make)
		{

			$result = mysql_query("SELECT CITY, name as STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc LIMIT 500");      
			while ($r = mysql_fetch_row($result))
			{
				$city = str_replace(" ","-", strtolower($r[0]));
				$state = str_replace(" ","-", strtolower($r[1]));

				$url = "http://repairpal.com/$make-repair-in-$city-$state";
				$webRequests[] = new WebRequest($url,$type);			  
			}
		}
		$this->loadWebRequests($webRequests);		
	}
/*
	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		if (strpos($html,"The Three Laws of Robotics are as follows:"))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("ERROR! ERROR! FORBIDDEN ACCESS!");
					
			$html=null;
		}

		if (strlen($html)<5000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
	}*/



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$urls = array();

		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		// load listings
		foreach($x->query("//a[contains(@class,'listing_link')]") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		// next page links
		foreach($x->query("//a[contains(text(),'Next')]") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		if (!empty($urls))
		{
			$thiz->loadUrlsByArray($urls);
		}
		
		if (! preg_match("/-repair-in-/",$url ))
		{
	

			$data = array();
			foreach($x->query("//*[@itemprop='name']") as $node)
			{
				$data['COMPANY'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//*[@itemprop='phone']") as $node)
			{
				$data['PHONE'] = self::cleanup($node->textContent);
			}

			foreach ($x->query("//div[@class='service_location_address']") as $node)
			{
				$data = array_merge($data, $ap->parse( $node->textContent ) );
				$data['ACCOUNT_TYPE'] = 'STANDARD';
			}

			foreach ($x->query("//p[@class='top_shop_address']") as $node)
			{
				$data = array_merge($data, $ap->parse( $node->c14n() ) );
				$data['ACCOUNT_TYPE'] = 'TOP SHOP';
			}
			
			foreach ($x->query("//div[@class='bp_website']//a") as $node)
			{
				$href = trim($node->getAttribute("href"));
				$data["WEBSITE"] =$href;
			}

			$specialties = array();
			foreach ($x->query("//div[@class='bp_specialties']//td") as $node)
			{
				$specialties[] = trim($node->textContent);
			}
			$data['SPECIALTIES'] = join(", ",$specialties);

			foreach($x->query("//div[@class='stars rating_5_0 rating']") as $node)
			{
				$data['AVG_RATING'] = $node->getAttribute("data-rating");
			}

			foreach($x->query("//span[@class='hidden count']") as $node)
			{
				$data['NUM_REVIEWS'] = self::cleanup($node->textContent);
			if ($data['NUM_REVIEWS']  == 0)
				$data['AVG_RATING'] = 0;

			}



			// Demandforce requirements
			$data['FIRST_NAME']="Not Provided";
			$data['LAST_NAME']="Not Provided";
			if(preg_match("/ |[a-z]/", @$data['ZIP']))
				$data['COUNTRY'] = 'Canada';
			else
				$data['COUNTRY'] = 'United States';


			$data['SOURCE_URL'] = $url;
			log::info($data);
			if (isset($data['SOURCE_URL']))
				db::store($type,$data,array('SOURCE_URL'));	
		
		}
	}
}

$r= new repairpal();
$r->parseCommandLine();

