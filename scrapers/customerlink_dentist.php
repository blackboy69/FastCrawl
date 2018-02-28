<?
include_once "config.inc";
include_once "search_engine_google.php";

class customerlink_dentists extends baseScrape
{
   public static $_this=null;
	public $google = null;
	public $i = 0; //debug;

	public function loadTerms()
	{
		return array ("site:reviews.customerlink.com Dentist", "site:reviews.customerlink.com Orthodontist", "site:reviews.customerlink.com Peridontist", "site:reviews.customerlink.com Endodontist");
	}

	public function runLoader()
   {
		
		$type = get_class();		
		db::query("DELETE FROM load_queue where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DELETE FROM $type");
	

		$this->proxy = "localhost:9666";
		$this->threads=1;

		//$this->debug=true;
		
	//	log::$errorLevel = ERROR_ALL;
		$this->google = new search_engine_google();
		$this->useCookies = false;
		$this->timeout = 5;
	
		//R::freeze();
		$terms = $this->loadTerms(); 

		//$terms = array ("site:facebook.com Auto Shop");
		// grab the first 3 pages..
		for ($i = 0;$i<10;$i++)
		{
			foreach ($terms as $term)
			{
				$url= $this->google->url($term,$i);
				$this->loadUrl($url);
			}			
		}			

		

		//$this->loadUrl("http://reviews.customerlink.com/biz/westside-automotive-houston");
/*
			db::query("		
			UPDATE load_queue
			 
			 SET processing = 1

			 WHERE
				 url IN (SELECT url FROM raw_data WHERE type ='$type')
				 AND type ='$type'
			");*/

		$this->queuedGet('customerlink_dentists::loadCallBack');
		
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
				
				// remove this item.
				db::query("DELETE FROM load_queue WHERE url='$url' and type='$type'");
				return;
			}
			//sleep(2);
		}
		else
		{
			self::getInstance()->threads = 4;
		}
		
		baseScrape::loadCallBack($url,$html,$arg3);
	}
	
	static function parse($url,$html)
	{
		$type = get_class();	

		echo ".";
		$host = parse_url($url,PHP_URL_HOST);
		
		if (preg_match("/google/",$host))
		{
			self::loadLinksFromGoogle($url,$html);			
		}
		else if (preg_match("/$type/",$host))
		{		
			self::loadCustomerLink($url,$html);
		}
		else
		{
			log::error("Unknown url $url");
		}
	}

	static function loadLinksFromGoogle($url,$html)
	{
		// load urls
		$urls = self::getInstance()->google->parse($html);
		foreach ($urls as $urlInfo )
		{			
			$u = parse_url($urlInfo['url']);
			$toLoad = $u['scheme']."://".$u['host'].$u['path'];

			if (preg_match("/customerlink.com/",$url))
			{
				log::info($toLoad);
				self::getInstance()->loadUrl($toLoad);	
			}
		}
	}

	static function loadCustomerLink($url,$html)
	{
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$data=array();            
		$data['url'] = $url;

		foreach ($x->query("//h1[@id='org-name']") as $node)
		{
			$data['name']  = $node->textContent;
		}

		foreach(array('street-address','locality','region','postal-code') as $name)
		{
			foreach ($x->query("//span[@class='adr']//span[@class='$name']") as $node)
			{
				$key = str_replace("-","_",$name);
				$data[$key]  = trim($node->textContent);
			}
		}

		foreach(array('tel','url') as $name)
		{
			foreach ($x->query("//span[@class='$name']") as $node)
			{
				$data[$name]  = trim($node->textContent);
			}
		}

		foreach ($x->query("//div[@id='orgDescription']//div[@class='bd']") as $node)
		{
			$data['description']  = trim($node->textContent);
		}
			
			
		foreach ($x->query("//div[@id='infoContainer']") as $node)
		{
			$hours = trim($node->textContent);
			if (preg_match("/Hours:(.+)Description/",$hours,$matches))
			{
				$data = array_merge($data, self::getInstance()->parseHours($matches[1]));
			}
		}
		
		// parse the hours
		if (array_key_exists("name", $data))
		{

			log::info($data['name']);

			db::store($type,$data,array('name','tel'));
			
		}
	}
	
	function parseHours($text)
	{
		$days = array("Mon"=>1,"Tue"=>2,"Wed"=>3,"Thu"=>4,"Fri"=>5,"Sat"=>6,"Sun"=>7);
		$daysRev = array_flip($days);
		$data = array();

		// first set all days to closed, algorithm below will set the hours for when they are open.
		foreach($days as $day => $junk)
		{
			$data[$day] = 'Closed';
		}

		
		// will take Monday - Friday and return array("Monday","Tuesday","Wednesday","Thursday","Friday")
		if (preg_match("/([a-z]+)-([a-z]+) (.+)/i",$text, $matches))
		{		
			$days['Monday']=$days['Mon'];
			$days['Friday']=$days['Fri'];

			for($i = $days[$matches[1]]; $i<= $days[$matches[2]] ; $i++)
			{
				$data[$daysRev[$i]] = $matches[3];
			}
		}
		else if (preg_match("/^([a-z]+) (- )?(.+)/i",$text, $matches))
		{
			$data[$matches[1]] = $matches[3];
		}
		return $data;
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
$r = new customerlink_dentists();

$r->runLoader();
$r->parseData();

$r->generateCSV();
$r->saveZip("C:\\dev\\htdocs\\demandforce\\dentists");

log::info("Parsed $r->i urls");
