<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class bigotires extends baseScrape
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

		*/
		// cananda top 100 cities by population
		//		db::query("UPDATE raw_data set parsed = 0 ");
	//	db::query("DELETE FROM load_queue where type='$type'");
//		
		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		

		$webRequests= array();
		db::query("DROP TABLE bigotires");
		db::query("UPDATE raw_data set parsed = 0 where type='bigotires' and parsed = 1   ");
		//db::query("UPDATE raw_data set parsed = 0 where type='bigotires' and parsed = 1 and url not like '%search%'   ");
		//db::query("UPDATE raw_data set parsed = 0 where type='bigotires' and parsed = 1 and url like '%portland%'  ");

		$this->loadUrlsByZip("http://www.bigotires.com/Locations/%ZIP%");		

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


		$listingx =  new  XPath($html);	
		foreach ($listingx->query("//div[@class='span4 mb20']/p") as $listingNode)
		{		
			$x = new Xpath($listingNode);
			$data = array();

			$addressTel=$listingNode->c14n();
			$data = array_merge($data,$ap->parse($addressTel));
			$data = array_merge($data,$pp->parse($addressTel));

if (!isset($data['CITY'])) continue;
			$data['COMPANY'] = "Big O Tires {$data['CITY']} {$data['STATE']}"			;

			// Demandforce requirements
			$data['FIRST_NAME']="Not Provided";
			$data['LAST_NAME']="Not Provided";
			$data['COUNTRY'] = 'United States';
unset($data['RAW_ADDRESS']);
			$data['SOURCE_URL'] = $url;
			log::info($data);
			db::store($type,$data,array('PHONE','ADDRESS'));			
		}
	}
}

$r= new bigotires();
$r->parseCommandLine();

