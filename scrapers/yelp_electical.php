<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class yelp_electrical extends baseScrape
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
		$this->threads=5;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		/*

	   db::query("
			DELETE FROM raw_data WHERE type ='yelp_electrical' and LENGTH(html) < 3000
		");
	   db::query("
		UPDATE load_queue
		SET PROCESSING=0		 
		 WHERE			 
  	       url NOT IN (SELECT url FROM raw_data WHERE type ='yelp_electrical')
			 AND
			 PROCESSING=1
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='yelp_electrical' and parsed = 1  ");
*/
		// cananda top 100 cities by population
		
		$this->maxRequestsPerProxy= 100; // 
		$this->noProxy=false;
		$this->useHmaProxy=false;

//		$this->debug = true;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");


//		$this->loadUrlsByCity("http://www.yelp.com/search?find_desc=Electrical&find_loc=%CITY%,%STATE%&ns=1&rpp=40",'',250)	;
		$this->switchProxy(null,true);

	}

	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		static $numRequests = array();
		


		$thiz = self::getInstance();
		if (strpos($html,"Sorry, you're not allowed to access this page."))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Sorry, you're not allowed to access this page.");
					
			$html=null;
		}

		if (strlen($html)<5000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
		sleep(1);
	}



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

		if (strpos($url,"yelp.com/search"))
		{
			// get biz links
			$urls = array(); 
			foreach($x->query("//a[contains(@class,'biz-name')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// get next page links
			foreach($x->query("//a[contains(@class,'page-option')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			if (!empty($urls))
			$thiz->loadUrlsByArray($urls);	
		}
		else
		{
			
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();


			$data = array();

			foreach ($x->query("//h1[@itemprop='name']") as $node)
			{
				$data['COMPANY'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='telephone']") as $node)
			{
				$data['PHONE'] = trim($node->textContent);
			}
			foreach ($x->query("//div[@id='bizUrl']//a") as $node)
			{
				$href = trim($node->getAttribute("href"));
				$data["WEBSITE"] = urldecode($thiz->urlVar($href,"url"));
			}
			foreach ($x->query("//span[@itemprop='streetAddress']") as $node)
			{
				$data['ADDRESS'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='addressLocality']") as $node)
			{
				$data['CITY'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='addressRegion']") as $node)
			{
				$data['STATE'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='postalCode']") as $node)
			{
				$data['ZIP'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='reviewCount']") as $node)
			{
				$data['NUM_REVIEWS'] = trim($node->textContent);
			}

			// pull category
			$categories=array();
			foreach ($x->query("//p[@id='bizCategories']//span[@itemprop='title']") as $node)
			{
				$categories[] = trim($node->textContent);
			}
			$data['CATEGORIES'] = join(",", $categories);

			//print_R($data);;
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

				if (empty($data['PHONE']))
					return;


				log::info($data);
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			
		}
	}
}

$r= new yelp_electrical();
$r->parseCommandLine();

