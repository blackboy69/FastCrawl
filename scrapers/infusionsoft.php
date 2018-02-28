<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class infusionsoft extends baseScrape
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
		db::query("UPDATE raw_data set parsed = 0 where type='infusionsoft' and parsed = 1   ");
		*/
		// cananda top 100 cities by population
		//		db::query("UPDATE raw_data set parsed = 0 ");

		$this->noProxy=true;

		//db::query("DELETE FROM load_queue where type='$type'");
		//db::query("DELETE FROM raw_data where type='$type'");
//			db::query("UPDATE load_queue set processing=1 where type='$type' and processing=0 and  url IN (SELECT url from raw_data where type='$type')");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
		db::query("DROP TABLE $type");

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		
		// load these first.
		$this->loadUrl("http://marketplace.infusionsoft.com/search?view_mode[listings]=page_1");


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
	//	$pp = new phone_parser();
//		$ep = new Email_Parser();
		$kv = new KeyValue_Parser();
		$urls = array();

		// parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	

		if (strpos($url, 'infusionsoft.com/search') > 0)
		{
			// next page links
			foreach($x->query("//a[contains(@href,'search?page=')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// Listings
			foreach($x->query("//li[contains(@class,'views-row')]//a[text()='More']") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);
			
		}
		else
		{
			$data = array();
			foreach($x->query("//div[contains(@class,'provider-sidebar-details')]//article//span[@class='field-label']") as $node)
			{
				$key = str_replace(":","", self::cleanup($node->textContent));
				$value =  strip_tags(self::cleanup(str_replace("<","   <",$node->nextSibling->nextSibling->c14n())));
				$value = trim (preg_replace('/[^(\x20-\x7F)]*/','', $value));

				if ($key == 'Address')
				{
					//$value = preg_replace('/[^(\x20-\x7F)]*/','', self::cleanup(str_replace("<","   <",$node->nextSibling->nextSibling->c14n())));
					$data = array_merge($data,$ap->parse($value));
					continue;
				}

				$data[$key] = $value;
			}

			$data['RATING'] = 0;
			foreach($x->query("//div[@class = 'field field-name-field-rating field-type-fivestar field-label-inline inline']//div[contains(@class,'star')]//span[@class='on']") as $node)
			{
				$data['RATING']++;
			}

			$data['SOURCE_URL'] = $url;

			log::info($data);

			if (isset($data['SOURCE_URL']))
				db::store($type,$data,array('SOURCE_URL'));	
		
		}
	}
}

$r= new infusionsoft();
$r->parseCommandLine();

