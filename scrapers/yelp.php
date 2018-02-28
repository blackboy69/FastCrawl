<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class yelp extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
		$this->proxy= "190.26.216.234:3128";
		$this->proxies[] = array("175.25.243.27:80",
"190.153.123.98:8080",
"118.244.190.35:80",
"122.72.11.200:80",
"122.72.99.8:80",
"116.50.153.66:3128",
"122.72.11.129:80",
"190.26.216.234:3128",
"122.72.112.148:80",
"125.39.66.130:80"); //from http://freedailyproxy.com/
$this->maxRetries = 25000;
$this->timeout = 15;
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=8;
		$this->debug=false;
		
		/*
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='yelp' and LENGTH(html) < 3000)
			 AND type ='yelp'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='yelp'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='yelp')
				  AND processing = 0
			     AND type ='yelp'
		    )
		
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		*/
		$this->loadUrlsByZip('http://www.yelp.com/search?find_desc=Beauty+and+Spas&find_loc=%ZIP%&ns=1');
	}

	static function loadCallBack($url,$html,$arg3)
	{
		$thiz = self::getInstance();

		if (strpos($html,"Sorry, you're not allowed to access this page."))
		{
			log::info("Sorry, you're not allowed to access this page.");
			$html=null;
		}
				if (strlen($html)<1000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
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
			foreach($x->query("//a[contains(@id,'bizTitleLink')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// get next page links
			foreach($x->query("//a[contains(@class,'pager-page')]") as $node)
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
				$data['NAME'] = trim($node->textContent);
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
			//print_R($data);;
			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data['NAME']);		
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			
		}
	}
}

$r= new yelp();
$r->parseCommandLine();

