<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class yelp_vet extends baseScrape
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
		$this->threads=1;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
/*		 db::query("		delete from raw_data WHERE type ='yelp_vet' and url like '%captcha%' or url like '%adredir?%'");

	   db::query("
		delete from load_queue

		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='yelp_vet' and ( LENGTH(html) < 3000) or url like '%captcha%')
			 AND type ='yelp_vet'
			 AND processing = 1
			 )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='yelp_vet')
				  AND processing = 0
			     AND type ='yelp_vet'
		    )
		
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='yelp_vet' and parsed = 1  ");
*/
		// cananda top 100 cities by population
		
		
		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");


// SKIP THE ONES WE IMPORTED FIRST
//      $result = mysql_query("SELECT CITY, STATE FROM geo.CANADIAN_CITIES WHERE order by pop desc limit 1200");        
      $result = mysql_query("SELECT * FROM  geo.canadian_cities WHERE CITY NOT  IN ( SELECT CITY FROM geo.canadian_cities WHERE STATENAME IS NOT NULL) order by pop desc limit 1200");
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode(strtoupper($r[0]));
			$state = urlencode("CANADA");
			$urls[] =  "http://www.yelp.com/search?find_desc=Veterinarian&find_loc=".urlencode("$city, $state")."&ns=1&rpp=40"	;
		}

		$this->loadUrlsByArray($urls);

		$this->switchProxy(null,true);
	}

	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		if (strpos($html,"Sorry, you're not allowed to access this page.") || preg_match("/captcha/", $url) )
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Sorry, you're not allowed to access this page.");
					
			$html=null;
		}
		
		static $requestCount=0;
		if ($requestCount++ > $thiz->maxRequestsPerProxy)
		{
			log::info("requestCount > maxRequestsPerProxy : $requestCount requests");
			$requestCount=0;
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
//		log::info($url);

		if (strpos($url,"yelp.com/search"))
		{
			// get biz links
			$urls = array();
			foreach($x->query("//a[contains(@class,'biz-name')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// get next page links
			foreach($x->query("//a[contains(@class,'page')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			if (!empty($urls))
				$thiz->loadUrlsByArray($urls);	
			else 
			{
				$thiz->queue->ExpireUrl($url,$type);
				log::info("Could not find links to load?!!!");
			}
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
			foreach ($x->query("//div[@class='biz-website']") as $node)
			{
				$data["WEBSITE"] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='streetAddress']") as $node)
			{
				$data['ADDRESS'] = trim($node->textContent);
			}

			if (empty($data['ADDRESS'] ))
			{
				foreach ($x->query("//li[@class='address']") as $node)
				{
					$data['ADDRESS'] = trim($node->textContent);
				}
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
			foreach ($x->query("//div[contains(@class,'biz-page-header')]//span[@itemprop='reviewCount']") as $node)
			{
				$data['NUM_REVIEWS'] = trim($node->textContent);
			}

			foreach ($x->query("//div[contains(@class,'biz-page-header')]//meta[@itemprop='ratingValue']") as $node)
			{
				$data['AVG_RATING'] = trim($node->getAttribute("content"));
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

				if (empty($data['COMPANY']))
					return;


				log::info("{$data['CITY']}, {$data['STATE']},  {$data['COMPANY']}");		
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			else log::info("$url\n returned nothing");
		}
	}
}

$r= new yelp_vet();
$r->parseCommandLine();

