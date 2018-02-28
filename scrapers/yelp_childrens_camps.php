<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class yelp_childrens_camps extends baseScrape
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
		
	// make sure we catch any extra timeouts redirects etc
	
	   db::query("
		update load_queue set processing=0	 
		 WHERE url NOT IN (SELECT url FROM raw_data WHERE type ='yelp_childrens_camps')	
and
type ='yelp_childrens_camps'	
			 ");
			 
			/*db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		*/
		// cananda top 100 cities by population
		
		
		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");
		$this->reloadPublicProxyList();
		$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		
		$keywords = array("Afterschool program", "Ballet camps", "Ballet clubs", "Ballet lessons", "Childrens tutoring services", "Childrens Music lessons", "Childrens Summer Camps", "Childrens afterschool program", "Childrens day camp", "Childrens educational services", "Gymnastics camps", "Gymnastics clubs", "Gymnastics lessons", "Kids Summer Camps", "Kids afterschool program", "Kids baseball camps", "Kids baseball clubs", "Kids basketball camps", "Kids basketball clubs", "Kids day camp", "Kids education camps", "Kids educational programs", "Kids music lessons", "Kids soccer camps", "Kids soccer clubs", "Student tutoring services", "Summer Camps", "Swim Camps", "Swim clubs", "Swim lessons", "Tutoring Services");
		
		$locations = array("Bay Area, CA","Chicago, IL", "Orange County, CA");
		
		foreach($locations as $location)
		{
			foreach ($keywords as $keyword)
			{
				$k=urlencode($keyword);
				$l=urlencode($location);
				$urls[] = "http://www.yelp.com/search?find_desc=$k&find_loc=$l";
			}
		}
		$this->loadUrlsByArray($urls);
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
log::info($url);

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

			foreach ($x->query("//meta[@itemprop='ratingValue']") as $node)
			{
				$data['AVG_RATING'] = trim($node->getAttribute("content"));
			}	

			
			foreach ($x->query("//ul[@class='ylist']//dl") as $nodeTop)
			{	
				$xInner = new Xpath($nodeTop);
				$k=$v=null;
				foreach ($xInner->query("//dt") as $node)
				{
					$k = self::cleanup($node->textContent);
				}
				foreach ($xInner->query("//dd") as $node)
				{
					$v = self::cleanup($node->textContent);
				}
				
				if ($k!=null)
					$data[$k] = $v;
			}
			// pull category
			$categories=array();
			foreach ($x->query("//span[@class='category-str-list']//a") as $node)
			{
				$categories[] = self::cleanup($node->textContent);
			}
			if (!empty($categories))
				$data['CATEGORIES'] = join(",", $categories);

			
			
			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;

				if(preg_match("/ |[a-z]/", @$data['ZIP']))
					$data['COUNTRY'] = 'Canada';
				else
					$data['COUNTRY'] = 'United States';

				/*if (empty($data['PHONE']))
					return;*/

				log::info(db::normalize($data));
				log::info("{$data['CITY']}, {$data['STATE']},  {$data['COMPANY']}");		
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			
		}
	}
}

$r= new yelp_childrens_camps();
$r->parseCommandLine();

