<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

/*
http://www.ip-address.org/reverse-lookup/reverse-ip.php
http://www.yougetsignal.com/tools/web-sites-on-web-server/
*/
class kukui extends baseScrape
{
    public static $_this=null;
	
	function __construct()
	{
		parent::__construct();

		$this->bing = new search_engine_bing();
		$this->google = new search_engine_google();
		$this->yahoo = new search_engine_yahoo();
	}


   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=2;
		$this->debug=false;
		$this->maxRetries   = 1;
		$this->sleepInterval= 2; 
//		$this->retryEnabled=false;
		//db::query("DROP TABLE $type");	
	//	db::query("UPDATE raw_data set parsed = 0 where type='KUKUI'");

		/*db::query("UPDATE raw_data set parsed = 0 where type='KUKUI' and parsed = 1 ");
		db::query("DROP TABLE $type");
	
		
		
		
		db::query("UPDATE load_queue set processing = 0 where type='KUKUI' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='KUKUI' and parsed = 1 ");	

	*/	
		//db::query("DELETE FROM load_queue where type='KUKUI' ");
		//db::query("DELETE FROM raw_data where type='KUKUI' ");

		
		$this->loadUrl($this->yahoo->url('"website by KUKUI"'));
		$this->loadUrl($this->bing->url('"website by KUKUI"'));
		$this->loadUrlsByArray(self::allTheLinks());
	}

	public static function parse($url,$html)
	{
		$thiz = self::getInstance();

		if (preg_match("/yahoo|bing/",$url))
			$thiz->parseSearchEngine($url,$html);
		else
			$thiz->parseListings($url,$html);
	}

	public static function parseSearchEngine($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$host = parse_url($url,PHP_URL_HOST);

		$listings=array();
		if (preg_match("/yahoo/",$url))
			$listings = $thiz->yahoo->parse($html,true,true);
		else if(preg_match("/bing/",$url))			
			$listings = $thiz->bing->parse($html,true,true);
		else
			log::error("Unknown url $url");
log::info($url);
		foreach($listings as $listing)
		{
print_R($listing);
			$lurl = $thiz->relative2absolute($url,$listing['URL']);
			$data = array();
			$data['SITE'] = parse_url($lurl,PHP_URL_HOST);
			$data['TITLE'] = $listing['TITLE'];
			$data['SOURCE_URL'] = $listing['URL'];
			$data['SEARCH_URL'] = $url;
			
			if (preg_match("/bing|yahoo/",$data['SITE']))
			{
				log::info("loading {$listing['URL']}");
				$thiz->loadUrl($listing['URL']);
			}
			else
			{
				$targetUrl = parse_url($lurl,PHP_URL_HOST);
				log::info("loading $targetUrl/Directions/");
				$thiz->loadUrl("$targetUrl/Directions/");
			}
		
		}
	}
	public static function parseListings($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$host = parse_url($url,PHP_URL_HOST);

		// grab the directions link
$data= array();
		foreach ($x->query("//a[contains(text(),'DIRECTIONS')]") as $node)
		{
			$href = $thiz->relative2absolute($url, $node->getAttribute('href'));
			$thiz->loadUrl($href);
		}

		foreach ($x->query("//*[contains(@name,'DirectionsCompanyName')]") as $node)
		{
			$data['CompanyName'] = $node->getAttribute("value");
		}
		
		foreach ($x->query("//*[contains(@name,'DirectionsPhone')]") as $node)
		{
			$data['Phone'] = $node->getAttribute("value");
		}
		
		foreach ($x->query("//*[contains(@name,'hdnDirectionsAddress')]") as $node)
		{
			$data = array_merge($data, $ap->parse($node->getAttribute("value")));
		}

		if (isset($data['CompanyName'] ))
		{
			$data['SOURCE_URL'] = $url;
			print_r($data);
			db::store($type,$data , array('SOURCE_URL'));
		}
	
	}

	public static function allTheLinks()
	{
		return file("kukui.txt");
	}
}

$r= new KUKUI();
$r->parseCommandLine();

