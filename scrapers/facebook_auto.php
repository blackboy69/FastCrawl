<?
include_once "config.inc";
include_once "search_engine_google.php";

class facebook_auto extends baseScrape
{
   public static $_this=null;
	public $google = null;
	public $i = 0; //debug;
	public function runLoader()
   {
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' and url like '%google.com/%'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DELETE FROM $type");
	

		//$this->proxy = "localhost:9666";
		$this->threads=1;

		//$this->debug=true;
		
	//	log::$errorLevel = ERROR_ALL;
		$this->google = new search_engine_google();
		$this->useCookies = false;
		$this->timeout = 5;
	
		//R::freeze();
		$this->i  = 0;
		$terms = array ("site:facebook.com/pages Auto Repair", "site:facebook.com/pages Automotive", "site:facebook.com/pages Auto Shop");
		//$terms = array ("site:facebook.com Auto Shop");
		// grab the first 15 pages..
		for ($i = 0;$i<5
			;$i++)
		{
			foreach ($terms as $term)
			{
				$url= $this->google->url($term,$i).'+%STATE%';
				$this->i++;
				$this->loadUrlsByState($url);
			}			
		}			
		

		$this->queuedGet('facebook_auto::loadCallBack');
		
   }

	static function loadCallBack($url,$html,$arg3)
	{
		$type = get_class();	
		$host = parse_url($url,PHP_URL_HOST);
		if (preg_match("/google/",$host))
		{
			self::getInstance()->threads = 1;
			if (strlen($html) < 3000)
			{
				log::error("GOOGLE HAS BLOCKED THIS IP. UPDATE PROXY. $url");
				
				// requeue this item.
				db::query("UPDATE load_queue SET processing = 0 WHERE url='$url' and type='$type'");
				return;
			}
		//	sleep(100);
		}
		else
		{
			self::getInstance()->threads = 4;
		}
		
		baseScrape::loadCallBack($url,$html,$arg3);
	}
	
	static function parse($url,$html)
	{
		//log::info(" parse($url)");
		echo ".";
		$host = parse_url($url,PHP_URL_HOST);
		
		if (preg_match("/youtube/",$host))
		{
			return;
		}		
		else if (preg_match("/google/",$host))
		{
			self::loadFacebookLinksFromGoogle($url,$html);			
		}
		else if (preg_match("/facebook/",$host))
		{		
			self::saveFacebookInfo($url,$html);
		}
		else
		{
			log::error("Unknown url $url");
		}
	}

	static function loadFacebookLinksFromGoogle($url,$html)
	{
		//log::info("loadFacebookLinksFromGoogle $url");
		// load urls
		$urls = self::getInstance()->google->parse($html);		
		foreach ($urls as $urlInfo )
		{			
			$u = parse_url($urlInfo['url']);
			$toLoad = $u['scheme']."://".$u['host'].$u['path'];

			if (preg_match("/facebook.com\/pages/",$toLoad))
			{

				self::getInstance()->loadUrl($toLoad."?sk=info&_fb_noscript=1");	
			}
		}
	}

	static function saveFacebookInfo($url,$html)
	{
		//log::info("saveFacebookInfo");

		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$data=array();            
		$data['url'] = $url;

		foreach ($x->query("//span[contains(@class,'ginormousProfileName')]") as $node)
		{
			$data['name']  = $node->textContent;
		}

		foreach ($x->query("//span[@class='uiNumberGiant fsxxl fwb']") as $node)
		{
			$data['fans']  = $node->textContent;
		}

		foreach ($x->query("//div[@class='phs']//tr") as $node)
		{
			
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	
			

			foreach ($x2->query("//th[@class='label']") as $node2)
			{
				$label = str_replace(" ","_", $node2->textContent);
			}

			foreach ($x2->query("//td[@class='data']") as $node2)
			{
				$value = $node2->textContent;
			}
			if ($label)
			{
				$data[$label] = $value;
			}
		}

		if (array_key_exists("name", $data))
		{

			log::info($data['name']);

			try {
				
				db::store($type,$data,array('name'));
			}
			catch(Exception $e)
			{
				log::error ("Cannot store ".$data['name']);
				print_r($data);
			}		
		}
	}
	



	static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/","",$str);
	}
	
	static function cleanup($str)
	{
		while (preg_match("/  /",$str))
		{
			$str = preg_replace("/  /i"," ",$str);
		}
		return trim($str);
	}

}
$r = new facebook_auto();

//$r->runLoader();
$r->parseData();

$r->generateCSV();
$r->saveZip("C:\\dev\\htdocs\\demandforce\\auto");

log::info("Parsed $r->i urls");
