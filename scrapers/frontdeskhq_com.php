<?
include_once "config.inc";

class frontdeskhq_com extends baseScrape
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
//		db::query("UPDATE  raw_data set parsed=0 where type='frontdeskhq_com' and parsed=1 and url like '%googleapis.com%' ");
//		db::query("delete FROM  load_queue where type='frontdeskhq_com' and processing=0 and url like '%googleapis.com%' ");
		
		db::query("UPDATE  raw_data set parsed=0 where type='frontdeskhq_com' and parsed=1");
		db::query("DROP TABLE $type");	
		
		#db::query("delete FROM  load_queue where type='frontdeskhq_com' and url like '%frontdeskhq.com%' and url not like '%bing%' and url not like '%googele&'");
		#db::query("update load_queue set processing = 1 where type='frontdeskhq_com' and processing=0");

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
//$this->proxy="localhost:8888";
		$this->noProxy=true;

		$this->threads=5;
		$this->useCookies = false;
		$this->timeout = 30;
		$urlsToLoad = array();
		$this->maxRetries = 1;
		
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
			limit 100
		");
		$urls = array();
		//$urls[] = self::$google->url("site:frontdeskhq.com San Francisco");
		
		//$urls[] = self::$bing->url("ite:frontdeskhq.com /site/login");
//		$urls[] = self::$bing->url("site:frontdeskhq.com");
		

		while ($r = mysql_fetch_row($result))
      {
			$city=$r[0];
			$state = $r[1];
			$zip = sprintf("%05d", $r[2]);
			$lat = sprintf("%.15f", $r[3]);
			$lon = sprintf("%.15f", $r[4]);

			$urls[] = self::$bing->url("site:frontdeskhq.com  $city, $state");
			$citystate = urlencode("$city, $state");
			$urls[] = "https://www.googleapis.com/customsearch/v1?q=$citystate&cx=009492075546048734483:b2xtbff1s_g&key=AIzaSyB8t9c67Jibug1_vPeByFZSdLJ374c4o8k";



			//$urls[] = self::$google->url("site:frontdeskhq.com $state");
	
			//$urls[] = self::$yahoo->url("site: frontdeskhq.com /login ".urlencode($state));
		}

		$this->loadUrlsByArray($urls);

		 //grab the recon-ng supplemental data
		 $this->loadUrlsByArray(file("$type.txt"));

	}
	

	static function parse($url,$html)
	{

      $thiz = self::getInstance();
		$type = get_class();		
		$host = parse_url($url,PHP_URL_HOST);
		log::info($url);
//										log::info($host);
		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip

		if (preg_match("/bing/",$host))
		{
			if (empty($query['start']) || $query['start'] > 100) // only the first 10 pages
			{
				$toLoad=array();
				$hrefs = self::$bing->parse($html,true);			
				foreach ($hrefs as $href)
				{
					$hrefHost = parse_url($href,PHP_URL_HOST); 
					if (preg_match("/frontdeskhq/",$hrefHost))
						$toLoad[] = "http://$hrefHost";
					#else if (preg_match("#bing.com/search#",$href))			
					#	$toLoad[] = $href;
				}
				log::info($toLoad);
				self::getInstance()->loadUrlsByArray($toLoad);
			}
		}
		else if (preg_match("/google.com/",$host))
		{
				$toLoad=array();
				$hrefs = self::$google->parse($html,true);			
				foreach ($hrefs as $href)
				{
					$hrefHost = parse_url($href,PHP_URL_HOST); 
					if (preg_match("/frontdeskhq/",$hrefHost))
						$toLoad[] = "http://$hrefHost";
#					else
						#$toLoad[] = $href;
				}
				log::info($toLoad);// don't load google direct use custom search
				self::getInstance()->loadUrlsByArray($toLoad);
		}
		else if (preg_match("/googleapis/",$host))
		{
			$toLoad=array();
			$json = json_decode($html,true);
			if (isset($json["items"]))
			{
				foreach($json["items"] as $item)
				{				
						$hrefHost = parse_url($item['link'],PHP_URL_HOST); 
						$toLoad[] = "http://$hrefHost";
				}
				
				// load next pages
				$start = isset($query['start']) ? $query['start'] : 1 ;			
				while ($start < $json["searchInformation"]["totalResults"] )
				{
					if ($start > 100) // only 10 pages
						break;

					$start +=10;
					$q=urlencode($query['q']);
					$key=urlencode($query['key']);
					$cx=urlencode($query['cx']);

					#$toLoad[] = "https://www.googleapis.com/customsearch/v1?q=$q&cx=$cx&key=$key&start=$start";
				}

				log::info($toLoad);
				self::getInstance()->loadUrlsByArray($toLoad);
			}
		}
		/*
		else if (preg_match("/yahoo/",$host))
		{
			self::getInstance()->loadUrlsByArray(self::$yahoo->parse($html));
		}*/
		else if (preg_match("#frontdeskhq|byronwhitlock#",$host))
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

			for($i=1;$i<5;$i++)
			{
				foreach($x->query("//h$i[1]") as $node)
				{
					if (strpos($node->textContent,"{") === false)
					{

						$data['NAME'] =  self::cleanup($node->textContent);
						goto EXIT_LOOP;
					}
				}
			}			
			EXIT_LOOP:

			if (empty ($data['NAME']) && (! preg_match("/byron/", $host)) )
			{
				$host = parse_url($url,PHP_URL_HOST); 
				list($name,$junk) = explode(".",$host);
				$data['NAME'] = ucfirst($name);
			}
			
			foreach($x->query("//a[contains(text(),'Our website')]") as $node)
			{
				$data['WEBSITE'] = $node->getAttribute("href");
			}

			foreach($x->query("//ul[@class='business_contact_information']") as $node)
			{
				$data = array_merge($data, $pp->parse($node->c14n()));
				$data = array_merge($data, $ep->parse($node->c14n()));
			}
			
			$data["LOCATION"] = array();
			foreach($x->query("//div[@class='location']") as $node)
			{
				$data["LOCATION"] += $ap->parse($node->c14n());
				$data["LOCATION"] += $pp->parse($node->c14n());
			}
		
			if (!isset($data['EMAIL']))
			{
				$href= "http://byronwhitlock.com/fastcrawl/casper.php?type=render&p1=".urlencode("http://$host");
				
				if (! preg_match("/byron/", $host))
				{
					log::info("FAILED! Using Javasdcript Renderer");
					// try to use the other renderer
					$thiz->loadUrl($href);
				}
			}

			unset($data['RAW_ADDRESS']);
			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('NAME','PHONE','WEBSITE','EMAIL'));
		}
		else
		{
			log::error("Cannot parse $url");
		}


	}
}
$r = new frontdeskhq_com();
$r->parseCommandLine();
