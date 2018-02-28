<?
include_once "config.inc";
include_once "search_engine_yahoo.php";

class smilereminder extends baseScrape
{
   public static $_this=null;
	public $yahoo = null;
	public $i = 0; //debug;
	var $noProxy = false;

	public function runLoader()
   {

		$this->noProxy=false;

		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' and url like '%yahoo%'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		#db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("Drop table $type");

		$this->noProxy=false;
		$this->yahoo = new search_engine_yahoo();
		foreach(db::oneCol(" Select distinct city from geo.locations where (pop > 40000 or pop2>40000) and state<> 'pr';") as $state)
		{
			$urls[]= $this->yahoo->url("site:smilereminder.com $state");
		}
		foreach(db::oneCol(" Select distinct state from geo.locations ;") as $state)
		{
			$urls[]= $this->yahoo->url("site:smilereminder.com $state");
		}
	
		$this->loadUrlsByArray($urls);

		#$this->proxy = "localhost:9666";
		$this->threads=4;
		$this->timeout = 10;
		//$this->debug=true;
		$this->useCookies=false;

		$urls = file("smilereminder-5-25.txt");
		//$this->loadUrlsByArray($urls);
		 
		$urls = file("smilereminder.txt");
		//$this->loadUrlsByArray($urls);
		//$this->loadUrl("http://www.smilereminder.com/vs/denvilledentist");
/*
			db::query("		
			UPDATE load_queue
			 
			 SET processing = 1

			 WHERE
				 url IN (SELECT url FROM raw_data WHERE type ='$type')
				 AND type ='$type'
			");*/
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
		else if (strpos(strtolower($url),"smilereminder.com/vs/"))
		{		
			self::loadSmileReminder($url,$html);
		}
		else
		{
			log::error("Unknown url $url");
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
			$toLoad = $u['scheme']."://".$u['host'].$u['path'];

			if (preg_match("/smilereminder.com/",$url))
			{
				self::getInstance()->loadUrl($toLoad);	
			}
		}
	}


	static function loadSmileReminder($url,$html)
	{
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$data=array();            
		$data['url'] = $url;
		
		foreach ($x->query("//p[@class='practiceName primaryColor item']/span") as $node)
		{
			$data['name'] = $node->textContent;
		}

		foreach ($x->query("//p[@class='tagline primaryColor']") as $node)
		{
			$data['tagline'] = $node->textContent;
		}

		foreach (array('street-address','locality','region','postal-code','tel','url') as $key)
		{
			
			foreach ($x->query("//*[@class='$key']") as $node)
			{
				$key = str_replace("-","_",$key);
				$data[$key] = $node->textContent;
			}
		}

		

		foreach ($x->query("//div[@class='sidebar1']") as $node)
		{
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	
			
			$name = "";
			$value = "";
			foreach ($x2->query("//a[0]") as $node2)	
			{
				$data['url'] = $node2->getAttribute('href');

			}
			foreach ($x2->query("//script") as $node2)	
			{
				if (preg_match('/"(.+)"/', $node2->textContent, $matches))
				{
					$data['email'] = str_replace("mailto:","",str_rot13($matches[1]));
				}
			}
		}
	
		$reviews = 0;
		foreach ($x->query("//div[@class='reviewBlock hreview']") as $node)
		{
			$reviews++;
		}

		
		$data['Number of Reviews'] = $reviews;

		if (isset($data['name']))
		{
			print_r($data);
			db::store($type,$data,array('name','tel'),false);
		}
		
		
	}

	
	
	function parseHours($text)
	{
		$days = array("Mon"=>1,"Tue"=>2,"Wed"=>3,"Thu"=>4, "Fri"=>5,"Sat"=>6,"Sun"=>7);
		
		$daysAll= array("Mon"=>1,"Tue"=>2,"Wed"=>3,"Thu"=>4, "Fri"=>5,"Sat"=>6,"Sun"=>7,"Monday"=>1,"Tuesday"=>2,"Wednesday"=>3,"Thursday"=>4,"Thur"=>4,"Friday"=>5,"Saturday"=>6,"Sunday"=>7);

		$daysRev = array_flip($days);
		$data = array();

		
		// will take Monday - Friday and return array("Monday","Tuesday","Wednesday","Thursday","Friday")
		if (preg_match("/([a-z]+)-([a-z]+) (.+)/i",$text, $matches))
		{		
			$fromTime = trim($matches[1]);
			$toTime = trim($matches[2]);
			for($i = $daysAll[$fromTime]; $i<= $daysAll[$toTime] ; $i++)
			{
				$data[$daysRev[$i]] = trim($matches[3]);
			}
		}
		else if (preg_match("/^([a-z]+) (- )?(.+)/i",$text, $matches))
		{
			$data[$matches[1]] = trim($matches[3]);
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
$r = new smilereminder();

$r->parseCommandLine();
//$r->saveZip("C:\\dev\\htdocs\\demandforce\\dentists");

log::info("Parsed $r->i urls");
