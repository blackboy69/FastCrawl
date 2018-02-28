<?
include_once "config.inc";
include_once "search_engine_google.php";

class powered_by_intuit_health extends baseScrape
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
#	  db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
#	  db::query("drop table $type");
		
	//	db::query("DELETE FROM load_queue where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DELETE FROM $type");
	

		//$this->proxy = "localhost:9666";
		$this->threads=4;
		$this->noProxy=false;
		//$this->debug=true;
		
	//	log::$errorLevel = ERROR_ALL;
		$this->useCookies = false;
		$this->timeout = 15;
	
		//R::freeze();
		$terms[] = '"Powered by Intuit Health"';
		$states = array('AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY');

		foreach($states as $state)
		{
			foreach(db::oneCol(" SELECT distinct city from geo.locations where country='us' and pop > 40000 and state='$state'") as $city)
			{
			$terms[] = '"Powered by Intuit Health" '."$city, $state";
			}
		}

		for ($i = 0;$i<3;$i++)
		{
			foreach ($terms as $term)
			{
			#	self::parse($this->google->url($term,$i), $this->Get($this->google->url($term,$i)));
			}			
		}	

		// grab the first 3 pages..
		for ($i = 1;$i<5;$i++)
		{
			foreach ($terms as $term)
			{
				//$urls[]= $this->google->url($term,$i);
				self::parse($this->yahoo->url($term,$i), $this->Get($this->yahoo->url($term,$i)));
				self::parse($this->bing->url($term,$i), $this->Get($this->bing->url($term,$i)));
			}			
		}			
   }
	
	static function parse($url,$html)
	{
		$thiz = self::getInstance();
		$type = get_class();	
		if (preg_match("/google/",$url) && strlen($html) < 3000)
		{
			log::info("GOOGLE BLOCKED: $thiz->proxy");
			return;
		}
		
		if (preg_match("/bing/",$url) && strlen($html) < 3000) 
		{
			log::info("BING BLOCKED: $thiz->proxy");
			return;
		}
		

		echo ".";
		$host = parse_url($url,PHP_URL_HOST);
		
		if (preg_match("/google/",$host))
		{
			self::loadLinks(self::getInstance()->google->parse($html));			
		}
		else if (preg_match("/bing/",$host))
		{
			self::loadLinks(self::getInstance()->bing->parse($html));			

		}
		else if (preg_match("/yahoo/",$host))
		{
			self::loadLinks(self::getInstance()->yahoo->parse($html));			

		}
		else
		{
			self::parseContactUs($url,$html);
		}
	}

	static function parseContactUs($url,$html)
	{
	}

	static function loadLinks($urls)
	{$type = get_class();	
		foreach ($urls as $urlInfo )
		{			
			$data = array();
			$u = parse_url($urlInfo);
			$data['WebSite_Link'] = $u['scheme']."://".$u['host'].@$u['path'];

			$data['WebSite_Host'] = $u['scheme']."://".$u['host'];



			if ($u['host'] == 'healthcare.intuit.com')
			{
				$data['xid'] = $data['WebSite_Link'];
			}
			else
			{
				$data['WebSite_ContactUs'] = $data['WebSite_Host'] . "/index.cfm/fuseaction/site.content/type/contact.cfm";
				$data['xid'] = $data['WebSite_Host'];
			}
			db::store($type,$data,array('xid'));
			
		}
		log::info("Loaded ".sizeof($urls). " links");
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
$r = new powered_by_intuit_health();
$r->parseCommandLine();