<?
include_once "config.inc";


class mechanicnet extends baseScrape
{
   public static $_this=null;
	public static $bing = null;

	function __construct()
	{
			$type = get_class();
		parent::__construct();
		self::$bing = new search_engine_bing();
	}
	public function runLoader()
   {
		self::$bing = new search_engine_bing();


		//R::freeze();
		$type = get_class();		
		//db::query("DELETE FROM load_queue  where type='$type'");
		#db::query("DELETE FROM raw_data  where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='mechanicnet' and url like '%bing%' ");


		#db::query("DROP TABLE  $type");
		//db::query("DELETE FROM $type");

	
		$this->noProxy=true;
		$this->threads=1;

		$this->debug=false;

		for($i =3;$i<15000;$i++)
		{
			$urls[] = "http://www.mechanicnet.com/apps/shops/map?shop_id=$i";
		}
		$this->loadUrlsByArray($urls);
		
   }

	static function parse($url,$html)
	{		
		$thiz = self::getInstance();
		if (preg_match("/bing.com/", $url ))
		{
			$urls = self::$bing->parse($html,false);
			if ( sizeof($urls) >0) 
			{

				$path = parse_url($urls[0],PHP_URL_PATH);
				if (strlen($path) < 2 )
				{				
					log::info("Loading {$urls[0]}");
					$thiz->loadUrl($urls[0]);
					
				}
			}
			return;
		}
		elseif(  preg_match("#www.mechanicnet.com/apps/shops/map#", $url ))
		{
			self::parseMain($url,$html);
		}
		else
		{
			self::parseMatches($url,$html);
		}
	}

		static function parseMatches($url,$html)
	{

		$type = get_class();		
		$x = new Xpath($html);	
		$thiz = self::getInstance();
		$ep = new Email_Parser();
		$pp=new Phone_Parser();

		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query);

		$data = array();
		
		// get the list of salons
		//*[@id="Table3"]
		foreach ($x->query("//td[@class='shopInfoText']") as $node)
		{
			$data = array_merge($data, $pp->parse($node->textContent));
			$data = array_merge($data, $ep->parse($node->textContent));
		}
		$data['WEBSITE2'] = $url;
		
		if (preg_match("/shop_id=([0-9]+)/",$html,$matches))
		{
			$data['shop_id'] = $matches[1];
		}

		if (!empty($data['shop_id']))
		{
			$data['source url2'] = $url;
			log::info($data);
			db::store($type, $data,array('shop_id'),"MERGE");		 
		}
	}

	static function parseMain($url,$html)
	{

		$type = get_class();		
		$x = new Xpath($html);	
		$thiz = self::getInstance();

		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query);

		$data = array();
		
		// get the list of salons
		//*[@id="Table3"]
		foreach ($x->query("//font[@class='BoldHeader']") as $node)
		{
			$data['name'] = trim($node->textContent);
		}

		if (!array_key_exists('name', $data)) return;

		foreach ($x->query("//font[@class='BODYtext']") as $node)
		{
			$data['full_address'] = trim($node->textContent);
		}
						
		// 628 Irene St., Orlando, FL 32805, US
		if ( preg_match("/(.+), (.+), ([^\s]+) ([^\s]+), (.+)/",$data['full_address'],$matches) )
		{				
			$data['address'] = $matches[1];
			$data['city'] = $matches[2];
			$data['state'] = $matches[3];
			$data['zip'] = $matches[4];
			$data['country'] = $matches[5];
		}

		//1 Prince Arthur Street, Amherst, NS B4H 1V3, Canada
		if ( preg_match("/(.+), (.+), ([A-Z]{2}) (.+), Canada/",$data['full_address'],$matches) )
		{				
			$data['address'] = $matches[1];
			$data['city'] = $matches[2];
			$data['state'] = $matches[3];
			$data['zip'] = $matches[4];
			$data['country'] = 'Canada';
		}
		

		if (!empty($data['name']))
		{
			$data['source url'] = $url;
			$data['shop_id'] = $query['shop_id'];
			log::info($data['name']);
			db::store($type, $data,array('shop_id'),"MERGE");		 
			
			// load up data for secondary scrape
			$url = self::$bing->url("site:mechanicnet.com {$data['name']} {$data['city']},{$data['state']}");
			$thiz->loadUrl($url);
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
$r = new mechanicnet();
$r->parseCommandLine();
