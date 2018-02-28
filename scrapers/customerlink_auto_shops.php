<?
include_once "config.inc";
include_once "search_engine_google.php";

class customerlink_auto_shops extends baseScrape
{
   public static $_this=null;
	public $google = null;
	public $bing = null;
	public $i = 0; //debug;


	function __construct()
	{
		parent::__construct();

		$this->bing = new search_engine_bing();
		$this->google = new search_engine_google();
		$this->yahoo = new search_engine_yahoo();



	}

	public function runLoader()
   {
		
		$type = get_class();		
//		db::query("UPDATE  load_queue set processing = 0 where type='$type'");
	  //db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
	 // db::query("drop table $type");
		
		//db::query("DELETE FROM load_queue where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DELETE FROM $type");
	

		//$this->proxy = "localhost:9666";
		$this->threads=16;
		$this->noProxy=false;
		//$this->debug=true;
		
	//	log::$errorLevel = ERROR_ALL;
		$this->useCookies = true;
		$this->timeout = 15;
		
		$reqs = array();
      $result = mysql_query("SELECT CITY, STATE FROM geo.US_CITIES order by pop desc limit 50");        
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode(strtoupper($r[0]));
			$state = urlencode(ucfirst($r[1]));

			$this->loadUrl($this->yahoo->url("site:reviews.customerlink.com Auto Repair $city, $state"));
			$this->loadUrl($this->bing->url("site:reviews.customerlink.com Auto Repair $city, $state"));

			$this->loadUrl($this->yahoo->url("site:reviews.customerlink.com Automotive $city, $state"));
			$this->loadUrl($this->bing->url("site:reviews.customerlink.com Automotive $city, $state"));

			$this->loadUrl($this->yahoo->url("site:reviews.customerlink.com Auto Shop $city, $state"));
			$this->loadUrl($this->bing->url("site:reviews.customerlink.com Auto Shop $city, $state"));
		}


		$this->loadUrl($this->yahoo->url("site:reviews.customerlink.com Auto Repair"));
		$this->loadUrl($this->bing->url("site:reviews.customerlink.com Auto Repair"));

		$this->loadUrl($this->yahoo->url("site:reviews.customerlink.com Automotive"));
		$this->loadUrl($this->bing->url("site:reviews.customerlink.com Automotive"));

		$this->loadUrl($this->yahoo->url("site:reviews.customerlink.com Auto Shop"));
		$this->loadUrl($this->bing->url("site:reviews.customerlink.com Auto Shop"));

		$this->QueuedFetch();			
   }

	static function parse($url,$html)
	{
		$thiz = self::getInstance();
		$type = get_class();		
		$host = parse_url($url,PHP_URL_HOST);

		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip
		log::info("parsing $host ". strlen($html));		

		if (preg_match("/yahoo|bing/i", $host))
		{
			$yahooUrls = $thiz->bing->parse($html);
			$bingUrls = $thiz->yahoo->parse($html);

			print_R($yahooUrls);
			print_r($bingUrls);
			$thiz->loadUrlsByArray($yahooUrls);
			$thiz->loadUrlsByArray($bingUrls);
		}		
		else if (preg_match("/customerlink/",$host))
		{		
			self::loadCustomerLink($url,$html);
		}
		else
		{
			log::error("Unknown url $url");
		}
	}

	static function loadCustomerLink($url,$html)
	{
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/","<p>",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$data=array();            
		$data['SOURCEURL'] = $url;
		$ap = new Address_Parser();

	
		foreach ($x->query("//*[@itemprop]") as $node)
		{
			$key = self::cleanup($node->getAttribute('itemprop'));
			$value = self::cleanup($node->textContent);
			
			if (strtolower($key) == 'url')
				$key = 'WEBSITE';

			if (!empty($value))
			{
				if (isset($data[$key]))
					$data[$key] .= "|". $value;
				else
					$data[$key] =  $value;
			}
		}

		foreach ($x->query("//div[@class='rating']//img") as $node)
		{
			$data['rating']  = $node->getAttribute('alt');
		}
			
		foreach ($x->query("//strong[contains(text(),'Hours:')]//following-sibling::*") as $node)
		{
			$hours = trim($node->textContent);
			if (preg_match("/Hours:(.+)Description/",$hours,$matches))
			{
				for($i = 1 ; $i < sizeof($matches) ; $i++)
				{
					
					$hours = explode("|", $matches[$i]);
					foreach( $hours as $hour )
					{
						$data = array_merge($data, self::getInstance()->parseHours(trim($hour)));
					}
				}
			}
		}
		$data = db::normalize($data);

		// parse the hours
		if (array_key_exists("NAME", $data))
		{

			log::info($data);

			db::store($type,$data, array('SOURCEURL') );
			
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
				if (!empty($daysRev[$i]))
				{
					$data[$daysRev[$i]] = trim($matches[3]);
				}
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
$r = new customerlink_auto_shops();
$r->parseCommandLine();