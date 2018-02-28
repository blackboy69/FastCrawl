<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class realself extends baseScrape
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
		db::query("UPDATE raw_data set parsed = 0 where type='realself' and parsed = 1   ");
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
		



	
		$this->loadUrl("http://www.realself.com/find");		
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
		foreach($x->query("//a[contains(@href,'/find/')]") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		if (!empty($urls))
		{
			$thiz->loadUrlsByArray($urls);
		}
		else
		{

			$data = array();
			foreach($x->query("//*[@itemprop='name']") as $node)
			{
				$data['COMPANY'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//span[@class='tel']") as $node)
			{
				$data['PHONE'] = self::cleanup($node->textContent);
			}

			foreach ($x->query("//address") as $node)
			{
				$data = array_merge($data, $ap->parse( $node->c14n() ) );
			}

			foreach($x->query("//*[@itemprop='url']") as $node)
			{
				$data['WEB_SITE'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//*[@itemprop='ratingValue']") as $node)
			{
				$data['AVG_RATING'] = self::cleanup($node->getAttribute("content"));
			}

			foreach($x->query("//*[@itemprop='reviewCount']") as $node)
			{
				$data['NUM_REVIEWS'] = self::cleanup($node->getAttribute("content"));
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

$r= new realself();
$r->parseCommandLine();

