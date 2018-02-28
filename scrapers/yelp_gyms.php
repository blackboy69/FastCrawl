<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

// we can do this after a few loads makes things go faster...
//R::freeze();

class yelp_gyms extends baseScrape
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
		
		/*
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='yelp_gyms' and LENGTH(html) < 3000)
			 AND type ='yelp_gyms'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='yelp_gyms'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='yelp_gyms')
				  AND processing = 0
			     AND type ='yelp_gyms'
		    )
		
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		*/
		// cananda top 100 cities by population
		
		
		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");
		$this->reloadPublicProxyList();
		$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		
		$this->loadUrlsByCity("http://www.yelp.com/search?find_desc=Gyms&find_loc=%CITY%,%STATE%&ns=1&rpp=40",'',50000);
		

	}

	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		if (strpos($html,"Sorry, you're not allowed to access this page."))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Sorry, you're not allowed to access this page.");
					
			$html=null;
		}
/*
mysql> update load_queue set processing=0 where processing=1 and url in (select
url from raw_data where length(html) < 11000 and type = 'yelp_gyms') and type =
'yelp_gyms'
    -> ;
	*/
		if (strlen($html)<11000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
		sleep(1);
	}



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	
		log::info($url);
		log::info(strlen($html));

		if (preg_match("#yelp.com/search#",$url))
		{
			// get biz links
			$urls = array();
			foreach($x->query("//a[contains(@class,'biz-name')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// get next page links
			foreach($x->query("//a[contains(@class,'pagination-links')]") as $node)
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
			foreach ($x->query("//a[contains(@href,'biz_redir?url=')]") as $node)
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
			foreach ($x->query("//span[@class='category-str-list']//a") as $node)
			{
				$categories[] = self::cleanup($node->textContent);
			}
			$data['CATEGORIES'] = join(", ", $categories);

			
			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;

				if(preg_match("/ |[a-z]/", @$data['ZIP']))
					$data['COUNTRY'] = 'Canada';
				else
					$data['COUNTRY'] = 'United States';

				if (empty($data['PHONE']))
					return;

				log::info($data);
				log::info("{$data['CITY']}, {$data['STATE']},  {$data['COMPANY']}");	
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			
		}
	}
}

$r= new yelp_gyms();
$r->workChunkSize=100;

$r->parseCommandLine();

