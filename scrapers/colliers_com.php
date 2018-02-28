<?
include_once "config.inc";

class colliers_com extends baseScrape
{
    public static $_this=null;
	
	public $category = "Real Estate";
	public $description = "Colliers International is a leading global commercial real estate company offering comprehensive services to investors, property owners, tenants and developers around the world.";
	

   public function runLoader()
   {
		$type = get_class();		
/*

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=8;
		$this->debug=false;
	*/	
		//$this->noProxy=false;
		//$this->proxy = "localhost:8888";
		
	//			$this->threads=10;
		$this->useCookies = true;
		$webRequests = array();
			
		
		$result = mysql_query("SELECT CITY, STATE FROM geo.US_CITIES order by pop desc limit 2000");      
		while ($r = mysql_fetch_row($result))
		{
			$city = strtoupper($r[0]);
			//log::info($city);
			$postUrl = "http://www.colliers.com/api/sitesearch.svc/search?city=".urlencode($city);
			$data = '{"Keyword":"'.urlencode($city).'","GooglePreformedRequest":"","SearchPath":"/Home","Language":"en-US","HomePath":"/Home","DisableFiltering":true,"StartIndex":0,"Page":0,"ResultsIndexPage":0,"FilterTypes":["person"]}';
			$webRequests[] = new WebRequest($postUrl,$type,"POST", $data);
		}
		$this->clean();
		
		//$this->setReferer($postUrl,"http://www.colliers.com/en-us/us/us_search");
		$this->headers[] = "X-Requested-With: XMLHttpRequest";
		$this->headers[] = "Content-Type: application/json; charset=UTF-8";

		$this->cookieData = "ismobile=false; isTablet=false; geoDetected=United States; LocationSessionRedirect=true; hsfirstvisit=http%3A%2F%2Fwww.colliers.com%2Fen-us||1454294520335; _ga=GA1.2.1787048097.1454294520; expertsFirstTime=false; __atuvc=28%7C5; Market=/en-US/US";
		//$this->loadUrl("http://www.colliers.com/en-us/us/us_search");
		$this->loadWebRequests($webRequests);
		
//TotalResultsCount=250 EndIndex=250
		//$this->loadUrl("byronwhitlock.com/fastcrawl/casper.php?type=render&p1=http://www.colliers_com.com/Spa/43160-The-Breakers-Palm-Beach");

	}
	
	static function loadCallBack($url,$html)
	{
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 
		if (preg_match("#/Templates/Mobile/ScreenDetection.htm#",$url))
		{
			$href = self::relative2absolute($url, $query['ReturnUrl']);
			$thiz->loadUrl($href,true);
			return;
		}
		parent::loadCallBack($url,$html,$type);
	}

	

	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		if (preg_match("#colliers.com/api/sitesearch.svc/search#",$url))
		{
			$json = json_decode ($html,true);
			$urls=ARRAY();
			$webRequests = array();
			
			if ( $json['TotalResultsCount'] > 0 && $json['EndIndex'] < $json['TotalResultsCount']  )
			{
				$city = urlencode($query['city']);
				$StartIndex = $json['EndIndex']; // get the next set of results
				$data = '{"Keyword":"'.$city.'","GooglePreformedRequest":"","SearchPath":"/Home","Language":"en-US","HomePath":"/Home","DisableFiltering":true,"StartIndex":"'.$StartIndex.'","Page":1,"ResultsIndexPage":10000,"FilterTypes":["person"]}';				
				
				// load next page
				$postUrl = "http://www.colliers.com/api/sitesearch.svc/search?city=$city&StartIndex=$StartIndex";
				$webRequests[] = new WebRequest($postUrl,$type,"POST", $data);
				
			}
			foreach ($json['SiteSearchItems'] as $listing)
			{
				$urls[] = $listing['Url'];
			}
		//	log::info($json);
			log::info($urls);
			log::info($webRequests);
			
			$thiz->loadUrlsByArray($urls);
			$thiz->loadWebRequests($webRequests);
		}
		else if (preg_match("#/Templates/Mobile/ScreenDetection.htm#",$url))
		{
			return;
			//$href = self::relative2absolute($url, $query['ReturnUrl']);
			//$thiz->loadUrl($href,true);
		}
		else 
		{
			$data = array();
			$found = false;
			foreach ($x->query("//h1[text()='Meet Our Experts']") as $node)
			{
				$found = true;
				break;
			}
			if (!$found )
			{
				log::error("$url not found");
				return;
			}
			
			foreach ($x->query("//span[@itemprop='name']") as $node)
			{
				
				$data = array_merge($data, $np->parse($node->textContent));
			}
			
			foreach ($x->query("//div[@class='name']//h5") as $node)
			{
				$data['COMPANIES'] = $node->textContent;
				break;
			}
			
			foreach ($x->query("//span[@itemprop='jobTitle']") as $node)
			{
				$data['TITLE'] = $node->textContent;
			}
			if ($data['COMPANIES'] == $data['TITLE'])
				unset ($data['COMPANIES']);
			
			foreach ($x->query("//div[@itemprop='address']") as $node)
			{
				$data = array_merge($data, $ap->parse($node->textContent));
			}		
			
			foreach ($x->query("//article") as $node)
			{
				$data = array_merge($data,$ep->parse($node->textContent));
			}
			
			foreach ($x->query("//span[@itemprop='telephone']") as $node)
			{
				$data['PHONE'] = $node->textContent;
			}
			
			foreach ($x->query("//span[@itemprop='fax']") as $node)
			{
				$data['FAX'] = $node->textContent;
			}
			
			foreach ($x->query("//span[@itemprop='mobileNumber']") as $node)
			{
				$data['MOBILE_PHONE'] = $node->textContent;
			}

			foreach ($x->query("//p[@class='tel']") as $node)
			{
				$data['PHONE'] =$node->textContent;
			}

			foreach ($x->query("//div[@class='toggleEl']") as $node)
			{				
				$x2 = new Xpath($node);
				$values = array();
				$name = "";
				foreach ($x2->query("//h4") as $node2)
				{
					$name = self::cleanup($node2->textContent);
				}
				if (empty($name))
				{
					foreach ($x2->query("//h3") as $node2)
					{
						$name = self::cleanup($node2->textContent);
					}
				}
				
				foreach ($x2->query("//p") as $node2)
				{
					$values[] = self::cleanup($node2->textContent);
				}
				if (!empty($name) && ! empty($values))
				$data[$name] =join("\n\n", $values);
			}


			$data['SOURCE_URL'] = $url;
			log::info($data);		
			$id = db::store($type,$data,array('SOURCE_URL'));	
		}
	}
}

$r= new colliers_com();
$r->parseCommandLine();

