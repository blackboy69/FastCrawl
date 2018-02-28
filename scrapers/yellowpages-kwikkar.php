<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class yellowpages_kwikkar extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		$this->maxRetries = 100;
		$this->timeout = 15;
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
		db::query("UPDATE raw_data set parsed = 0 where type='yellowpages_kwikkar' and parsed = 1   ");
		*/
		// cananda top 100 cities by population
		//		db::query("UPDATE raw_data set parsed = 0 ");

		$this->noProxy=false;
//		$this->proxy = "localhost:9666";
		$this->useHmaProxy=false;

		// $this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		// log::info("Loaded ".sizeof($this->proxies)." Proxies");

		// $this->switchProxy(null,true);
		db::query("DROP TABLE $type");
		db::query("UPDATE raw_data set parsed = 0 where type='yellowpages_kwikkar'  and parsed = 1   ");
			db::query("UPDATE load_queue SET processing = 1 WHERE  type='yellowpages_kwikkar' and url IN (SELECT url FROM raw_data where type='yellowpages_kwikkar')					 ");

		$this->proxy = "127.0.0.1:9666";
		$this->loadUrlsByCity("http://www.yellowpages.com/search?tracks=true&search_terms=kwik+kar&geo_location_terms=%CITY%,%STATE%")	;
		$this->loadUrl("http://www.yellowpages.com/search?tracks=true&search_terms=kwikkar&geo_location_terms=Houston+TEXAS",true);

	}

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
	}



	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$xTop = new  XPath($html);	

		// get next page links
		foreach($xTop->query("//div[@class='pagination']//a") as $nodeTop)
		{
			$urls[] = self::relative2absolute($url,$nodeTop->getAttribute("href"));
		}

		if (!empty($urls))
			$thiz->loadUrlsByArray($urls);

		foreach($xTop->query("//div[@class='result']") as $nodeTop)
		{
			$thiz->parseIt($url, $nodeTop);
			echo ".";
		}
		/*
		//overwrite featured listings
		foreach($xTop->query("//div[@class='featured']//div[@class='sb-group']/ul") as $nodeTop)
		{
			$thiz->parseIt($url, $nodeTop);
			echo "+";
		}*/
	}

	function parseIt($url, $nodeTop)
	{
			$x = new XPath($nodeTop);
			$type = get_class();		
			$thiz = self::getInstance();

			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();

			$data = array();
//log::info($nodeTop->c14n());
			foreach ($x->query("//a[@class='business-name']") as $node)
			{
				$data['COMPANY'] = trim($node->textContent);
			}
			
			if (! preg_match("#Kwik#",$data['COMPANY']))
				return;
		
	
			
			foreach ($x->query("//a[@class='track-visit-website']") as $node)
			{
				$href = trim($node->getAttribute("href"));
				$data["WEBSITE"] =$href;
			}	
			

			$data = array_merge($data, $pp->parse(self::cleanup(str_replace("<", " <", $nodeTop->c14n())) ) );


			foreach ($x->query("//*[@itemprop='streetAddress']") as $node)
			{
				$data["ADDRESS"] = $node->textContent;
			}
			foreach ($x->query("//*[@itemprop='addressLocality']") as $node)
			{
				$data["CITY"] = str_replace(",","", $node->textContent);
			}
			foreach ($x->query("//*[@itemprop='addressRegion']") as $node)
			{
				$data["STATE"] = $node->textContent;
			}

			unset ($data['RAW_ADDRESS'] );
/*
			foreach ($x->query("//a[@class='rating']//span[@class='count']") as $node)
			{
				$data['RATING'] = trim($node->textContent);
			}

			foreach ($x->query("//span[@class='count']") as $node)
			{
				$data['NUM_REVIEWS'] = trim($node->textContent);
			}*/
			// pull category
			$categories=array();
			foreach ($x->query("//ul[@class='business-categories']//li") as $node)
			{
				$cat = trim($node->textContent);
				if (!empty($cat))
					$categories[] =  $cat ;
			}
//			$data['CATEGORIES'] = join(",", $categories);

//			$data['ACCOUNT_TYPE'] = $nodeTop->getAttribute("class");


			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;

				// Demandforce requirements
				$data['FIRST_NAME']="Not Provided";
				$data['LAST_NAME']="Not Provided";
				if(preg_match("/ |[a-z]/", @$data['ZIP']))
					$data['COUNTRY'] = 'Canada';
				else
					$data['COUNTRY'] = 'United States';


//				echo ".";		
				log::info($data);
				db::store($type,$data,array('COMPANY','PHONE','ADDRESS','ZIP', 'ACCOUNT_TYPE'));	
			}		
	}
}

$r= new yellowpages_kwikkar();
$r->parseCommandLine();

