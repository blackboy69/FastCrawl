<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class yelp_chiro extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		$this->maxRetries = 10;
		$this->timeout = 15;
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=10;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
/*		 db::query("		delete from raw_data WHERE type ='yelp_chiro' and url like '%captcha%' or url like '%adredir?%'");

	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='yelp_chiro' and ( LENGTH(html) < 3000) or url like '%captcha%')
			 AND type ='yelp_chiro'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='yelp_chiro'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='yelp_chiro')
				  AND processing = 0
			     AND type ='yelp_chiro'
		    )
		
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='yelp_chiro' and parsed = 1  ");
*/
		// cananda top 100 cities by population
		
		
		$this->noProxy=false;
		//$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");
		
		/*
		proxy::loadProxy("23.253.100.15:3128"); // from https://free-proxy-list.net/
		proxy::loadProxy("124.30.87.234:80");
		proxy::loadProxy("113.30.102.91:3128");
		proxy::loadProxy("1.179.165.142:3128");
		proxy::loadProxy("104.154.90.76:8080");
		proxy::loadProxy("23.244.68.94:80");
		proxy::loadProxy("201.202.246.162:8080");
		proxy::loadProxy("139.162.12.138:8080");
		proxy::loadProxy("54.183.203.161:8080");
		proxy::loadProxy("118.142.33.112:8088");
		proxy::loadProxy("222.155.136.36:3128");
		proxy::loadProxy("193.227.168.213:3128");
		proxy::loadProxy("87.101.149.195:8080");
		proxy::loadProxy("187.174.124.178:8080");
		proxy::loadProxy("155.14.140.50:8080");
		proxy::loadProxy("62.197.227.163:80");
		proxy::loadProxy("167.114.135.68:80");
		proxy::loadProxy("58.64.141.172:9999");
		proxy::loadProxy("185.124.149.22:80");
		proxy::loadProxy("103.243.94.143:8080");*/
		$this->useDbProxy=true;
		
		$this->switchProxy("http://www.yelp.com",true);

		$this->loadUrlsByLocation("http://www.yelp.com/search?find_desc=Chiropractor&find_loc=%CITY%,%STATE%&ns=1&rpp=40")	;

	}

	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;
/*
		$thiz = self::getInstance();
		if (strpos($html,"Sorry, you're not allowed to access this page.") || preg_match("/captcha/", $url) )
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Sorry, you're not allowed to access this page.");
					
			$html=null;
		}
		
		$proxy = new Proxy("http://www.yelp.com");
		
		
		static $requestCount=0;
		if ($requestCount++ > $thiz->maxRequestsPerProxy)
		{
			log::info("requestCount > maxRequestsPerProxy : $requestCount requests");
			$requestCount=0;
			$html=null;
		}*/

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

$r= new yelp_chiro();
$r->parseCommandLine();

