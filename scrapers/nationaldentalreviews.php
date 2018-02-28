<?
include_once "config.inc";

class nationaldentalreviews extends baseScrape
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
		$this->nextProxyUrl = "http://hidemyass.com/proxy-list/search-225371"; // USA ONLY. required for bing results to be accurate when doing proxy jumping
		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1 ");
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
				 url IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) < 500)
				 AND type ='$type'
		");*/

		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");

		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		

*/
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
		/**/db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
		db::query("DROP TABLE $type");
		
//		

		//$this->noProxy=false;
//db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
		$this->threads=4;
		$this->useCookies = true;
		//unlink($this->cookie_file);
		$this->timeout = 10;
		$urlsToLoad = array();
//		$this->proxy='localhost:8888';
	//	$this->noProxy=false;
		$urls = array();
		
	//	db::query("DELETE FROM load_queue where type='$type' and url like '%yahoo.com%'");
	//db::query("DELETE FROM load_queue where type='$type' and url like '%bing.com%'");


		$this->loadUrl(self::$yahoo->url("site:nationaldentalreviews.org"));
		$this->loadUrl(self::$bing->url("site:nationaldentalreviews.org"));
		

		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1 and url ='http://www.nationaldentalreviews.org/Default.aspx' ");
		$html = $this->get("http://www.nationaldentalreviews.org/Default.aspx");
		$searchPage = new HtmlParser($html);
		$form = $searchPage->loadViewState();	
		
		$form['ctl00_ajaxScriptManager_HiddenField'] = '';
		$form['ctl00$ContentMain$tbFirstName'] = 'First Name';

		$form['ctl00$ContentMain$ibSearch.x'] = '0';
		$form['ctl00$ContentMain$ibSearch.y'] = '0';
		$form['ctl00$ContentMain$hfSearch'] = '';
		$found=false;
		$this->setReferer("*", "http://www.nationaldentalreviews.org/Default.aspx");
		$lastNames = file("lastnames.txt");
		foreach($lastNames as $lastName)
		{
			//if (trim(strtoupper($lastName)) == 'FELICIANO') $found = true;

			if (!$found) continue;

			log::info($lastName);
			$form['ctl00$ContentMain$tbLastName'] = $lastName;
			$html = $this->Post("http://www.nationaldentalreviews.org/Default.aspx", $form);

			$x = new Xpath($html);
			$urls = array();
			foreach($x->query("//input[contains(@id,'hfOfficeURL')]") as $node)
			{
				$urls[] = "http://".$node->getAttribute("value").".nationaldentalreviews.org/";
			}
			$this->loadUrlsByArray($urls);		
		}
		$this->queuedFetch();

	}
	
	static function loadCallBack($url,$html,$arg3)
	{
		$t = self::getInstance();
		$type = get_class();	
		$host = parse_url($url,PHP_URL_HOST);
		if (strpos($host,"yahoo")>0)
		{
			if (preg_match("/Sorry, Unable to process request at this time/", $html))
			{
				log::info("Blocked by yahoo:$url");
				$html=null;
			}
		}		
		if (strpos($host,"bing")>0)
		{/*
			if (strlen($html<3000))
			{
				log::info("Blocked by bing:$url");
				$html=null;
			}*/
		}		
		baseScrape::loadCallBack($url,$html,$arg3);
	}

	static function parse($url,$html)
	{
		$t = self::getInstance();
		$type = get_class();		
		$host = parse_url($url,PHP_URL_HOST);

		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip
		log::info("parseng $host ". strlen($html));		

		if (preg_match("/yahoo|bing/",$url))
		{
			$y = self::$bing->parse($html);
			$b = self::$yahoo->parse($html);

			print_R($y);
			print_r($b);
			$t->loadUrlsByArray($y);
			$t->loadUrlsByArray($b);
		}		
		else if (preg_match("/nationaldentalreviews/",$url))
		{


			$x = new Xpath($html);
			$data=array();            			
			$ap = new Address_Parser();

			foreach($x->query("//span[@id='ctl00_PracticeName']") as $node)
			{
				$data['NAME'] = $node->textContent;
			}
		
			foreach($x->query("//span[@itemprop='address']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
			}
			foreach($x->query("//a[@id='ctl00_PracticeWebsiteUrl']") as $node)
			{
				$data['WEBSITE'] = $node->getAttribute("href");
			}
		
			foreach($x->query("//a[@id='ctl00_PracticeEmail']") as $node)
			{
				$data['EMAIL'] = $node->textContent;
			}
			
			foreach($x->query("//span[@itemprop='tel']") as $node)
			{
				$data['PHONE'] = $node->textContent;
			}
			
			foreach($x->query("//a[@class='pluck-review-rollup-review-meta-count']") as $node)
			{
				$data['NUM_REVIEWS'] = $node->textContent;
			}
			
			
			
			if (isset($data['NAME']))
			{
				try {		
					$data['SOURCE_URL'] = $url;
					log::info($data);
					db::store($type,$data,array('NAME', 'RAW ADDRESS'));
				}
				catch(Exception $e)
				{
					log::error ("Cannot store ".$data['Name']);
					log::error($e);
					//print_r($data);
					exit;
				}		
			}
		}
		else
		{
			log::error("Invalid url $url");
		}



	}
}
$r = new nationaldentalreviews();
$r->parseCommandLine();
