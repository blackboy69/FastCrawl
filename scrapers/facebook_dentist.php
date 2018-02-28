<?
include_once "config.inc";
include_once "search_engine_bing.php";

class facebook_dentists extends baseScrape
{
   public static $_this=null;
	public $bing = null;
	public $i = 0; //debug;
	public function runLoader()
   {
		
		$type = get_class();		
		#db::query("DELETE FROM load_queue where type='$type' and url like '%facebook.com/%'");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		#db::query("DELETE FROM raw_data where type='$type' ");
		# db::query("DELETE FROM $type");
	

		#$this->proxy = "localhost:9666";
		$this->threads=1;
		$this->useCookies= false;
		//$this->debug=true;
		
	//	log::$errorLevel = ERROR_ALL;
		$this->bing = new search_engine_bing();
		$this->useCookies = false;
		$this->timeout = 5;
	
		//R::freeze();
		$this->i  = 0;
		$terms = array ("site:facebook.com/pages Dentist", "site:facebook.com/pages orthodontist");

		// grab the first 15 pages..
		/*
		for ($i = 0;$i<=10;$i++)
		{
			foreach ($terms as $term)
			{
				$url= $this->bing->url($term,$i).'+%STATE%';
				$this->i++;
				$this->loadUrlsByState($url);
			}			
		}			
		*/
   }

	static function parse($url,$html)
	{
		echo ".";
		
		$host = parse_url($url,PHP_URL_HOST);
		
		if (preg_match("/youtube/",$host))
		{
			return;
		}		
		else if (preg_match("/bing/",$host))
		{
			self::loadFacebookLinksFromBing($url,$html);			
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

	static function loadFacebookLinksFromBing($url,$html)
	{
		// load urls
		$urls = self::getInstance()->bing->parse($html);

		foreach ($urls as $urlInfo )
		{			
			
			$u = parse_url($urlInfo['url']);
			
			$toLoad = $u['scheme']."://".$u['host'].$u['path'];
			self::getInstance()->loadUrl($toLoad."?sk=info&_fb_noscript=1");	
			
		}
	}

	static function saveFacebookInfo($url,$html)
	{
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
$r = new facebook_dentists();

$r->runLoader();
$r->parseData();

$r->generateCSV();

log::info("Parsed $r->i urls");
