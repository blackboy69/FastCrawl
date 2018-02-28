<?
include_once "config.inc";

class lightspeedwebstore2_com extends baseScrape
{
    public static $_this=null;
	public static $google = null;
	public static $bing = null;	
		public static $yahoo = null;	
	public static $csv=null;
	public static $csvMap=array();

	private $urlsToLoad = array();
	function __construct()
	{
			$type = get_class();
		parent::__construct();
		self::$bing = new search_engine_bing();
		self::$yahoo = new search_engine_yahoo();
		self::$google = new search_engine_google();
		$this->noProxy=true;
//		$this->switchProxy();

		//$this->nextProxyUrl = "http://hidemyass.com/proxy-list/search-225371"; // USA ONLY. required for bing results to be accurate when doing proxy jumping
		db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1 ");
		db::query("DROP TABLE $type");	
	}

	
   public function runLoader()
   {
		$type = get_class();
//		db::query("DELETE FROM LOAD_QUEUE where type='$type'");
		$type = get_class();		
/*
		db::query("		
			UPDATE load_queue
			 
			 SET processing = 0

			 WHERE			 
				 url IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) < 3000 And url like 'http://www.google.com%')
				 AND type ='$type'
		");*/

		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		
*/
		//$this->proxy = "localhost:9666";


		// db::query("DELETE FROM load_queue where type='$type'");
//		db::query("DELETE FROM Raw_data where type='$type'");
	//	db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
//				db::query("DROP TABLE $type");	
//$this->proxy="locahost:9666";
		$this->noProxy=true;

		$this->threads=5;
		$this->useCookies = true;
		$this->timeout = 15;
		$urlsToLoad = array();
		$this->maxRetries = 3;
		
		$urls = array();
	  $result = mysql_query("
			SELECT 
				geo.us_cities.city as CITY, 
				geo.us_cities.state AS STATE,
				MAX( geo.locations.zip ) as ZIP ,  
				max(lat) as LAT ,
				max(lon) as LON
			FROM  geo.us_cities 
			INNER JOIN geo.locations ON (geo.us_cities.city = geo.locations.city AND geo.US_CITIES.STATE  = GEO.locations.state)
			GROUP BY geo.us_cities.city, geo.us_cities.state 
			ORDER BY geo.us_cities.POP DESC
			limit 500
		");
		$urls = array();
		$urls[] = self::$google->url("site:lightspeedwebstore.com San Francisco");
		
		//$urls[] = self::$bing->url("ite:lightspeedwebstore.com /site/login");
//		$urls[] = self::$bing->url("site:lightspeedwebstore.com");
		

		while ($r = mysql_fetch_row($result))
      {
			$city=$r[0];
			$state = $r[1];
			$zip = sprintf("%05d", $r[2]);
			$lat = sprintf("%.15f", $r[3]);
			$lon = sprintf("%.15f", $r[4]);

//			$urls[] = self::$bing->url("site:lightspeedwebstore.com  $city, $state");

			//$urls[] = self::$yahoo->url("site: lightspeedwebstore2_com.com /login ".urlencode($state));
		}
		$this->loadUrlsByArray($urls);

		 //grab the recon-ng supplemental data

		 $this->loadUrlsByArray(file("$type.txt"));

	}
	

	static function parse($url,$html)
	{
		$t = self::getInstance();
		$type = get_class();		
		$host = parse_url($url,PHP_URL_HOST);
		log::info($url);
//										log::info($host);
		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip

		if (preg_match("/bing|google/",$host))
		{
			if (empty($query['start']) || $query['start'] > 100) // only the first 10 pages
			{
				$toLoad=array();
				$hrefs = self::$bing->parse($html,true);			
				foreach ($hrefs as $href)
				{
					$hrefHost = parse_url($href,PHP_URL_HOST); 
					if (preg_match("/lightspeedwebstore/",$hrefHost))
					{						
						$toLoad[] = "http://$hrefHost/contact-us";
					}
				}
				log::info($toLoad);
				self::getInstance()->loadUrlsByArray($toLoad);
			}
		}
		/*else if (preg_match("/google/",$host))
		{
			$urls = self::$google->parse($html,true);
			log::info($urls);
			return;
			self::getInstance()->loadUrlsByArray($urls);
		}
		else if (preg_match("/yahoo/",$host))
		{
			self::getInstance()->loadUrlsByArray(self::$yahoo->parse($html));
		}*/
		else if (preg_match("#lightspeedwebstore#",$host))
		{
			$data = array();
			$x = new  XPath($html);	
			$ap = new Address_Parser();
			$pp = new Phone_Parser();
			$ep = new Email_Parser();
			
			foreach($x->query("//title") as $node)
			{
				if (strpos($node->textContent,":") !== false)
				{
					list($junk, $name) = explode(":", $node->textContent);
					$data['NAME'] = self::cleanup($name);
				}				
			}
			if (empty ($data['NAME']) )
			{				
				foreach($x->query("//h1[1]") as $node)
				{
					if (strpos($node->textContent,"{") === false)
						$data['NAME'] =  self::cleanup($node->textContent);
				}
			}

			if (preg_match("/order online!/", $data['NAME'])) 
				$data['NAME'] = "";


			if (empty ($data['NAME']) )
			{
				$host = parse_url($url,PHP_URL_HOST); 
				list($name,$junk) = explode(".",$host);
				$data['NAME'] = ucfirst($name);
			}

			foreach($x->query("//footer") as $node)
			{
				$data = array_merge($data, $ap->parse($node->c14n()));
				$data = array_merge($data, $pp->parse($node->c14n()));
				$data = array_merge($data, $ep->parse($node->c14n()));
			}
			
			if (empty($data['ADDRESS']) || empty($data['PHONE']))
			{
				foreach($x->query("//*[contains(@id, 'footer')]") as $node)
				{
					$data = array_merge($data, $ap->parse($node->c14n()));
					$data = array_merge($data, $pp->parse($node->c14n()));
					$data = array_merge($data, $ep->parse($node->c14n()));
				}
			}
			
			unset($data['RAW_ADDRESS']);
			$data['SOURCE_URL'] = $url;
			log::info($data);
			db::store($type,$data,array('NAME', 'PHONE','EMAIL'));
		}
		else
		{
			log::error("Cannot parse $url");
		}


	}
}
$r = new lightspeedwebstore2_com();
$r->parseCommandLine();
