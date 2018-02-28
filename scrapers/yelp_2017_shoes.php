<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class yelp_shoes extends baseScrape
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
			 url IN (SELECT url FROM raw_data WHERE type ='yelp_shoes' and LENGTH(html) < 3000)
			 AND type ='yelp_shoes'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='yelp_shoes'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='yelp_shoes')
				  AND processing = 0
			     AND type ='yelp_shoes'
		    ) OR url like '%adredir%' AND type ='yelp_shoes'
		
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		*/
		// cananda top 100 cities by population
		
		
		
		//$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");
		//$this->reloadPublicProxyList();
		//
		
		
		
		
		// for 2017
		// we are using the proxy we paid for to switch on each search to a new ip		
		// https://stormproxies.com/clients/backconnect		
		$this->proxy = "163.172.48.109:15001";
		$this->noProxy=false;
		$this->useDbProxy=false;
		//$this->maxRetries=PHP_INT_MAX; // this prevents the proxy from switching.
		
		// 
		
		
		//$this->loadUrlsByLocation("http://www.yelp.com/search?find_desc=Shoes&find_loc=%CITY%,%STATE%");
		
	}

	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		if (preg_match("#www.yelp.com/visit_captcha#",$url))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Captcha page.");
					
			$html=null;
		}
		
		if (strpos($html,"Sorry, you're not allowed to access this page."))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Sorry, you're not allowed to access this page.");
					
			$html=null;
		}
		

		if (strlen($html)<5000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
		sleep(2);
	}



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	
		log::info($url);
			$ep = new Email_Parser();
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();

		if (preg_match("#yelp.com/search#",$url))
		{
			// get biz links
			$urls = array();
			foreach($x->query("//a[contains(@class,'biz-name')]") as $node)
			{
				$href = $node->getAttribute("href");
				if (!preg_match("#adredir#",$href))
				{
					$urls[] = self::relative2absolute($url,$href);
				}
			}

			// get next page links
			foreach($x->query("//a[contains(@class,'pagination-links')]") as $node)
			{
				$href = $node->getAttribute("href");
				if (!preg_match("#adredir#",$href))
				{
					$urls[] = self::relative2absolute($url,$href);
				}
			}

			if (!empty($urls))
				$thiz->loadUrlsByArray($urls);	
		}
		else if (preg_match("#yelp.com#",$url))
		{
			



			$data = array();

			foreach ($x->query("//h1[contains(@class,'biz-page-title')]") as $node)
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
			$data['CATEGORIES'] = join(",", $categories);

			log::info($data);
			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;

				if(preg_match("/ |[a-z]/", @$data['ZIP']))
					$data['COUNTRY'] = 'Canada';
				else
					$data['COUNTRY'] = 'United States';

				if (empty($data['PHONE']))
					return;

				
				log::info("{$data['CITY']}, {$data['STATE']},  {$data['COMPANY']}");		
				$id = db::store($type,$data,array('SOURCE_URL'));	
				
				if (!empty($data['WEBSITE']))
				{
					$thiz->loadUrl($data['WEBSITE']."?id=$id");
				}
			}		
		}
		else // check the id
		{
			$query = array();
			parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip

			if (!empty($query['id']))
			{
				$id = $query['id'];

				$data = db::query("SELECT * FROM $type where id = $id");
				
				$data=array_merge($data,$ep->parse(strip_tags($html)));
				$data=array_merge($data,$pp->parse(strip_tags($html)));
				
				log::info("!!!SECONDARY PARSE!!!!");

				// did we find email or phone numbers?
				if ( isset($data['EMAIL']) || isset($data['PHONE']) )
				{				
					log::info($data);
					db::store($type,$data,array('COMPANY_NAME', 'ADDRESS','CITY','SOURCE_URL'),true);
				}
				// otherwise spider to the contact us page when both aren't already set.
				else if (! (isset($data['EMAIL']) && isset($data['PHONE'])) )
				{
					$x = new  XPath($html);	

					foreach($x->query("//a[contains( translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'contact')]") as $node)
					{
						$href = self::relative2absolute($url,$node->getAttribute("href")."?id=$id");
						
						log::info("Found Contact us page");
						log::info($href);
						$thiz->loadUrl($href);
					}
				}
			}
			else
			log::info("Unknown url");

		}
	}
}

$r= new yelp_shoes();
$r->parseCommandLine();

