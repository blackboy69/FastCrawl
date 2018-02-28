<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class driverside extends baseScrape
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
		db::query("UPDATE raw_data set parsed = 0 where type='driverside' and parsed = 1   ");
		*/
		// cananda top 100 cities by population
		//		db::query("UPDATE raw_data set parsed = 0 ");

		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		
		// load these first.
		$this->loadUrl("http://www.driverside.com/verified-mechanics/");
		$this->loadUrlsByZip("http://www.driverside.com/find-mechanic/?zip=%ZIP%");

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

		foreach($x->query("//a[contains(@href,'verified-mechanics')]") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}

		// next page links
		foreach($x->query("//div[@class='float_right_rt paging']//a[contains(@title,'Page')]") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		$thiz->loadUrlsByArray($urls);
		
		// type
$account_type ="";
		foreach($x->query("//h1[contains(text(),'Reputation Verified')]") as $node)
		{
			$account_type = "Reputation Verified";
		}

		foreach($x->query("//div[@id='automechanic_list']//div[contains(@class,'hreview-aggregate')]") as $nodeListing)
		{
	
			$xListing = new xPath($nodeListing);

			$data = array();
			foreach($xListing->query("//a[contains(@class,'fn org')]") as $node)
			{
				$data['COMPANY'] = self::cleanup($node->textContent);
				$data['SOURCE_URL'] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// PHONE, pares the whole html of the listing
			$data = array_merge($data, $pp->parse( $nodeListing->c14n() ));	

			foreach ($xListing->query("//div[@class='adr']") as $node)
			{
				$data = array_merge($data, $ap->parse( $node->textContent ) );
			}


			foreach ($xListing->query("//a[@class='visit_url_link']") as $node)
			{
				$href = trim($node->getAttribute("href"));
				$data["WEBSITE"] =$href;
			}	
			
			foreach ($xListing->query("//span[@class='stars_gold']//p") as $node)
			{
				$data['RATING'] = (preg_replace("/[^0-9]/","", $node->getAttribute('style'))  / 100 );
			}

			foreach ($xListing->query("//a[@class='sm_grey_link']") as $node)
			{
				$data['NUM_REVIEWS'] = preg_replace("/[^0-9]/","", $node->textContent);
			}

			$data["ACCOUNT_TYPE"] = $account_type;
			$data['ACTUAL_SOURCE_URL'] = $url;

			// Demandforce requirements
			$data['FIRST_NAME']="Not Provided";
			$data['LAST_NAME']="Not Provided";
			if(preg_match("/ |[a-z]/", @$data['ZIP']))
				$data['COUNTRY'] = 'Canada';
			else
				$data['COUNTRY'] = 'United States';


				echo ".";		
			//log::info($data);

			if (isset($data['SOURCE_URL']))
				db::store($type,$data,array('SOURCE_URL'));	
		
		}
	}
}

$r= new driverside();
$r->parseCommandLine();

