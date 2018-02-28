<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class demandforce_accountant extends baseScrape
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
//		$this->threads=2;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)		 
			 ");

		*/
		// cananda top 100 cities by population
		//db::query("UPDATE raw_data set parsed = 0 where parsed=1 and type = '$type' ");
	//	db::query("DELETE FROM load_queue where type='$type'");
//		
		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		$this->noProxy=false;
		$this->useDbProxy=FALSE;
		$this->reloadPublicProxyList();
		

		$webRequests= array();
		//db::query("DROP TABLE demandforce_accountant");
		//db::query("DELETE FROM load_queue where type='$type'");
		//db::query("DELETE FROM raw_data where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='demandforce_accountant' and parsed = 1   ");
		//db::query("UPDATE raw_data set parsed = 0 where type='demandforce_accountant' and parsed = 1 and url not like '%search%'   ");
		//db::query("UPDATE raw_data set parsed = 0 where type='demandforce_accountant' and parsed = 1 and url like '%portland%'  ");

		$this->loadUrlsByLocation("http://search.demandforce.com/businesses?q=%CITY%&p=1&lc=%LAT%%2C%LON%&ls=0.17367689898779304%2C0.2197265625&lz=11",null,2500);	
		
//		$this->loadUrl("http://search.demandforce.com/businesses?q=Dentists&p=1&lc=37.77494226194981%2C-122.41941550000001&ls=0.17367689898779304%2C0.2197265625&lz=11");

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

		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$kvp = new KeyValue_Parser();	
		$urls = array();
		log::info($url);
		//parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		
		$links = array();
		$data = array();
		if (preg_match("#search.demandforce.com/businesses#",$url))
		{
			$json_data = json_decode($html,true);
			$html = $json_data['results']['businessResults'];
			$x =  new  XPath($html);	
			// listings		
			foreach ($x->query("//a[@class='bname']") as $node)
			{
				  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			// grab next page links...
			foreach ($x->query("//*[@class='pages']//a") as $node)
			{
				  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			if (!empty($links))
				$thiz->loadUrlsByArray($links);
		}
		else
		{
			$x =  new  XPath($html);	
			foreach($x->query("//*[@class = 'business-card-name']") as $node)
			{
				$data['COMPANY'] = $node->textContent;
			}

			foreach($x->query("//*[@class = 'business-card-vertical']") as $node)
			{
				$data['CATEGORY'] = $node->textContent;
			}
			foreach($x->query("//*[@class = 'business-card-rating']") as $node)
			{
				$data['RATING'] = $node->textContent;
			}

			foreach($x->query("//*[@class = 'business-card-address']") as $node)
			{
				$data = array_merge($data,$ap->parse(	$node->c14n()));
			}
			
			foreach($x->query("//*[@class = 'business-card-contact']//*[@itemprop = 'telephone']") as $node)
			{
				$data['PHONE'] = $node->textContent;
			}

			foreach($x->query("//*[@class = 'business-card-contact']//*[@itemprop = 'email']") as $node)
			{
				$data['EMAIL'] = $node->textContent;
			}

			foreach($x->query("//*[@class = 'business-card-contact']//*[@itemprop = 'url']") as $node)
			{
				$data['WEBSITE'] = $node->textContent;
			}
			
			$staff = array();
			foreach($x->query("//*[@class = 'business-profile-staff-member']//h3") as $node)
			{
				$staff[] = $node->textContent;
			}
			$data['STAFF'] = join(",",$staff);

			// Demandforce requirements
			
			$data['COUNTRY'] = 'United States';
			unset($data['RAW_ADDRESS']);

			$data['SOURCE_URL'] = $url;
			log::info($data);
			db::store($type,$data,array('PHONE','ADDRESS'));			
		}
	}
}

$r= new demandforce_accountant();
$r->parseCommandLine();

