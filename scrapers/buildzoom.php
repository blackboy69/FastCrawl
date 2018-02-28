<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class buildzoom extends baseScrape
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

		*/
		// cananda top 100 cities by population
		//		db::query("UPDATE raw_data set parsed = 0 ");
	//	db::query("DELETE FROM load_queue where type='$type'");
//		
		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		

		$webRequests= array();
	//	db::query("UPDATE raw_data set parsed = 0 where type='buildzoom' and parsed = 1   ");
		//db::query("UPDATE raw_data set parsed = 0 where type='buildzoom' and parsed = 1 and url not like '%search%'   ");
		//db::query("UPDATE raw_data set parsed = 0 where type='buildzoom' and parsed = 1 and url like '%portland%'  ");

		$result = mysql_query("SELECT CITY, name as STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc LIMIT 2000");      
		while ($r = mysql_fetch_row($result))
		{
			$city = str_replace(" ","-", strtolower($r[0]));
			$state = str_replace(" ","-", strtolower($r[1]));

			$url = "http://www.buildzoom.com/search/$city";
			$webRequests[] = new WebRequest($url,$type);			  
		}

		$this->loadWebRequests($webRequests);		

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
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$kvp = new KeyValue_Parser();	
		$urls = array();
		log::info($url);
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		// load listings
		foreach($x->query("//div[contains(@class,'cdesc')]//strong//a") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		// next page links
		foreach($x->query("//ul[@class='pagination']//a") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		if (!empty($urls))
		{
			$thiz->loadUrlsByArray($urls);
		}
		
		if ( preg_match("/contractor/",$url ))
		{
			foreach ($x->query("//div[@id='infotop']//tr") as $node)
			{
				list ($k,$v) = explode(":",$node->textContent);
				$k=self::cleanup($k);
				$v=self::cleanup($v);
				$data[$k] = $v;
			}
			

			// Demandforce requirements
			$data['FIRST_NAME']="Not Provided";
			$data['LAST_NAME']="Not Provided";
			if(preg_match("/ |[a-z]/", @$data['ZIP']))
				$data['COUNTRY'] = 'Canada';
			else
				$data['COUNTRY'] = 'United States';


			$data['SOURCE_URL'] = $url;
			log::info($data);
			if (isset($data['SOURCE_URL']))
				db::store($type,$data,array('SOURCE_URL'));	
		
		}
	}
}

$r= new buildzoom();
$r->parseCommandLine();

