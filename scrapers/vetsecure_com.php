<?
include_once "config.inc";

class vetsecure_com extends baseScrape
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
		//db::query("DROP TABLE $type");	
	}

	
   public function runLoader()
   {
		
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
		db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
				db::query("DROP TABLE $type");	
$this->proxy="locahost:9666";
		$this->noProxy=true;

		$this->threads=1;
		$this->useCookies = true;
		$this->timeout = 5;
		$urlsToLoad = array();
		$this->maxRetries = 1;
		
		$urls = array();
		foreach(db::oneCol("SELECT DISTINCT CITY from geo.locations order by pop desc limit 100") as $state)
		{
		
				$urls[] = self::$bing->url("site:vetsecure_com.com /login ".urlencode($state));
			//$urls[] = self::$yahoo->url("site:vetsecure_com.com /login $state")."&gws_rd=ssl#filter=0";		
//				$urls[] = self::$google->url("site:vetsecure_com.com /login $state")."&gws_rd=ssl#filter=0";
			//	$urls[] = self::$google->url("site:vetsecure_com.com /login $state")."&gws_rd=ssl#filter=0&filter=0";		
		}	

				$states = array ('Alabama','Alaska','Alberta','Arizona','Arkansas','British Columbia','California','Colorado','Connecticut','D. Columbia','Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Manitoba','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Brunswick','New Hampshire','New Jersey','New Mexico','New York','Newfoundland & Labrador','North Carolina','North Dakota','Northwest Territories','Nova S cotia','Nunavut','Ohio','Oklahoma','Ontario','Oregon','Pennsylvania','Prince Edward Island','Puerto Rico','Quebec','Rhode Island','Saskatchewan','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming','Yukon Territory');
		
		foreach ($states as $state)
		{
			$urls[] = self::$bing->url("site:vetsecure.com /login ".urlencode($state));
			//$urls[] = self::$yahoo->url("site: vetsecure.com /login ".urlencode($state));
		}
		$this->loadUrlsByArray($urls);

	}
	

	static function parse($url,$html)
	{
		$t = self::getInstance();
		$type = get_class();		
		$host = parse_url($url,PHP_URL_HOST);
					log::info($url);
										log::info($host);
		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip

		if (preg_match("/bing/",$host))
		{
			$urls = self::$bing->parse($html,true);
			log::info($urls);
			self::getInstance()->loadUrlsByArray($urls);
		}
		else if (preg_match("/google/",$host))
		{
			$urls = self::$google->parse($html,true);
			log::info($urls);
			return;
			self::getInstance()->loadUrlsByArray($urls);
		}
		else if (preg_match("/yahoo/",$host))
		{
			self::getInstance()->loadUrlsByArray(self::$yahoo->parse($html));
		}
		else if (preg_match("/vetsecure/",$host))
		{
			$type = get_class();		
			$thiz = self::getInstance();
			
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$ep = new Email_Parser();
			$kvp = new KeyValue_Parser();


			$data = array();
			$x = new  XPath($html);	
			
			foreach($x->query("//a[@class='name']") as $node)
			{
				$data['NAME'] = $node->textContent;
				$data['WEBSITE'] = $node->getAttribute("href");
			}
			foreach($x->query("//address") as $node)
			{
				$data = array_merge($data, $ap->parse($node->c14n()));
			}
			foreach($x->query("//div[@class='view-footer']") as $node)
			{
				$data = array_merge($data, $pp->parse($node->c14n()));
				$data = array_merge($data, $ep->parse($node->c14n()));
			}
			$data['SOURCE_URL'] = $url;

			db::store($type,$data,array('SOURCE_URL'));
		}
		else
		{
			log::error("Invalid url $url");
		}


	}
}
$r = new vetsecure_com();
$r->parseCommandLine();
