<?
include_once "config.inc";
include_once "search_engine_yahoo.php";


class agencyrevolution extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		$this->maxRetries = 3;
		$this->timeout = 5;
		//$this->useCookies=false;
		$this->noProxy=true;
	//	$this->proxy='127.0.0.1:9996';
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=8;

		//db::query("DELETE FROM raw_data where type='agencyrevolution'   ");
	//	db::query("DELETE FROM load_queue where type='agencyrevolution'   ");
	//	db::query("DROP TABLE agencyrevolution");
//	db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		
		$urls = file("agencyrevolution.txt");

		$this->yahoo = new search_engine_yahoo();
		$urls[]= $this->yahoo->url('"Powered by Agency Revolution"');

		$this->loadUrlsByArray($urls);
	}

	static function loadCallBack($url,$html,$arg3)
	{
		$type = get_class();	
		$host = parse_url($url,PHP_URL_HOST);
		if (preg_match("/yahoo/",$host))
		{
			if (preg_match("/Sorry, Unable to process request at this time/", $html))
			{
				log::info("Blocked by yahoo:$url");
				self::getInstance()->useCookies=false;
				self::getInstance()->switchProxy($url);
				return;
			}
		}		
		baseScrape::loadCallBack($url,$html,$arg3);
	}
	
	static function parse($url,$html)
	{
		$type = get_class();	

		echo ".";
		$host = parse_url($url,PHP_URL_HOST);
		
		if (preg_match("/yahoo/",$host))
		{
			self::loadLinksFromyahoo($url,$html);			
		}
		else
		{
			self::parseListing($url,$html);
		}
	}

	static function loadLinksFromyahoo($url,$html)
	{
		log::info("Loading links from yahoo $url");
		// load urls
		$thiz =  self::getInstance();

		if (! is_object($thiz->yahoo == null))
			$thiz->yahoo = new search_engine_yahoo();

		$urls = $thiz->yahoo->parse($html);

		foreach ($urls as $resultUrl )
		{			
			$u = parse_url($resultUrl);
			$toLoad = $u['scheme']."://".$u['host']."/contact-us";
			self::getInstance()->loadUrl($toLoad);	

		}
	}

	public static function parseListing($url,$html,$break=false)
	{
		log::info("In parse");
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		$ep = new Email_Parser();

		$data = array();
		
		#Name
		foreach ($x->query("//title") as $node)
		{
			$data['PAGE_TITLE'] = self::cleanup($node->textContent);
			$data['NAME'] = str_replace(" contact us","", $node->textContent);
		}
		
		
		foreach ($x->query("//div") as $node)
		{
			#address
			$data = array_merge($data,$ap->parse($node->textContent));			
			if (!empty($data['ZIP']))
				break;
		}

		#Phone numbers
		$data = array_merge($data,$pp->parse($html));


				#Email Address
		$data = array_merge($data,$ep->parse($html));

		if ( isset($data[""]) )
		{
			$data['UNKNOWN'] = $data[""];
			unset($data[""]);
		}
		
		$data['WEBSITE'] = dirname($url);

		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;
//			$data['RAW_ADDRESS'];

			log::info($data);
			db::store($type,$data,array('SOURCE_URL'));	
			return true;
		}		
		
	}
}

$r= new agencyrevolution();
$r->parseCommandLine();

