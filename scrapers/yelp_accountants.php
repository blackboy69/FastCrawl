<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class yelp_accountants extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
		$this->maxRetries = 150;
		$this->timeout = 15;
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		
		/*
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='yelp_accountants' and LENGTH(html) < 3000)
			 AND type ='yelp_accountants'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='yelp_accountants'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='yelp_accountants')
				  AND processing = 0
			     AND type ='yelp_accountants'
		    )
		
			 ");

		*/
		// cananda top 100 cities by population
		db::query("DELETE FROM LOAD_QUEUE where url like 'http://www.yelp.com/adredir%' and  type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");		
		//db::query("DROP TABLE $type ");		


		//$this->noProxy=false;
		//$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);

		$urls = array();

		foreach(file("$type.txt") as $line)
		{
			$city = urlencode(trim($line));
			$urls[] = "http://www.yelp.com/search?find_desc=Accountants&find_loc=$city%&ns=1&rpp=40";
			$urls[] = "http://www.yelp.com/search?find_desc=Bookkeepers&find_loc=$city%&ns=1&rpp=40";
			$urls[] = "http://www.yelp.com/search?find_desc=Payroll+Services&find_loc=$city%&ns=1&rpp=40";
			$urls[] = "http://www.yelp.com/search?find_desc=Financial+Advising&find_loc=$city%&ns=1&rpp=40";
			$urls[] = "http://www.yelp.com/search?find_desc=Investing&find_loc=$city%&ns=1&rpp=40";
		}

		$this->loadUrlsByArray($urls);			
	}

	static function loadCallBack($url,$html,$arg3)
	{
		$thiz = self::getInstance();

		if (strpos($html,"Sorry, you're not allowed to access this page."))
		{
			log::info("Sorry, you're not allowed to access this page.");
			$html=null;
		}
		
		if (strlen($html)<5000) {$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);

		$sleeptime = rand(1,5);
		log::info("Sleeping for $sleeptime");
		sleep($sleeptime);
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
            if (strpos($node->getAttribute("href"),"yelp.com/adredir") > 0)
               continue;
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// get next page links
			foreach($x->query("//a[contains(@class,'page-option')]") as $node)
			{
            if (strpos($node->getAttribute("href"),"yelp.com/adredir") > 0)
               continue;
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
			foreach ($x->query("//div[@class='biz-website']//a") as $node)
			{
				$href = trim($node->getAttribute("href"));
				$data["WEBSITE"] = urldecode($thiz->urlVar($href,"url"));
			}

			foreach ($x->query("//li[@class='address']") as $node)
			{
				$data = array_merge($data, $ap->parse($node->c14n()));
			}
			UNSET($data['RAW_ADDRESS']);
			/*
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
			*/
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
			
			if (empty(	$data['CATEGORIES'] ))
			{
				foreach ($x->query("//span[@class='category-str-list']") as $node)
				{
					$categories[] = trim($node->textContent);
				}
				$data['CATEGORIES'] = join(",", $categories);
			}

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


				log::info($data);		
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			else log::info("$url\n returned nothing");
		}
	}
}

$r= new yelp_accountants();
$r->parseCommandLine();

