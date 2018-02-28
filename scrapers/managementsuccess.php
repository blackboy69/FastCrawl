<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class managementsuccess extends baseScrape
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
		db::query("UPDATE raw_data set parsed = 0 where type='managementsuccess' and parsed = 1   ");
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
		$this->loadUrlsByArray(file("managementsuccess.txt"));


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
log::info($url);
		$data = array();
		foreach($x->query("//title") as $node)
		{
			list($company, $title) = @explode("|",$node->textContent,2);

			$data['COMPANY'] = self::cleanup($company);
		}

		foreach($x->query("//h2[contains(text(),'Shop Location & Information')]/following-sibling::table//tr") as $node)
		{
			log::info( self::cleanup($node->textContent));
//			$data = array_merge($data,$kv->parse($node->c14n()));
			
			if (! preg_match("/:/", self::cleanup($node->textContent)))
			{
				$data['EXTRA'][] = self::cleanup($node->textContent);
				continue;
			}
			list($key,$value) = explode(":", self::cleanup($node->textContent),2);

			//$key = str_replace(":","", self::cleanup($node->textContent));
			//$value =  strip_tags(self::cleanup(str_replace("<","   <",$node->nextSibling->nextSibling->c14n())));
			//$value = trim (preg_replace('/[^(\x20-\x7F)]*/','', $value));

			if ($key == 'Address')
			{
				//$value = preg_replace('/[^(\x20-\x7F)]*/','', self::cleanup(str_replace("<","   <",$node->nextSibling->nextSibling->c14n())));
				$data = array_merge($data,$ap->parse($value));
				continue;
			}

		$data[$key] = $value;
		}

		$data['SOURCE_URL'] = $url;

		log::info($data);

		if (isset($data['SOURCE_URL']))
			db::store($type,$data,array('SOURCE_URL'));	
	
	}
}

$r= new managementsuccess();
$r->parseCommandLine();

