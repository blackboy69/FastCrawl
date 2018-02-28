<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class provisor_intuit extends baseScrape
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
//		$this->threads=2;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)		 
			 ");

		*/
		// cananda top 100 cities by population
		//db::query("UPDATE raw_data set parsed = 0 where parsed=1 and type = '$type' ");
	//	db::query("DELETE FROM load_queue where type='$type'");
//		
		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "localhost:9666";
		

		$webRequests= array();
		//db::query("DROP TABLE provisor_intuit");
		//db::query("DELETE FROM load_queue where type='$type'");
		//db::query("DELETE FROM raw_data where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='provisor_intuit' and parsed = 1   ");
	//	db::query("UPDATE raw_data set parsed = 0 where type='provisor_intuit' and parsed = 1 and url NOT like '%proadvisor.intuit.com/quickbooks-pro/%'   ");
//		db::query("UPDATE raw_data set parsed = 1 where type='provisor_intuit' and parsed = 0 and url like '%proadvisor.intuit.com/quickbooks-pro/%'   ");
		//db::query("UPDATE raw_data set parsed = 0 where type='provisor_intuit' and parsed = 1 and url like '%portland%'  ");

		//db::query("UPDATE raw_data set parsed = 1 where type='provisor_intuit' and parsed = 0 and url NOT like '%proadvisor.intuit.com/quickbooks-pro/%'   ");
		//db::query("UPDATE raw_data set parsed = 0 where type='provisor_intuit' and parsed = 1 and url like '%proadvisor.intuit.com/quickbooks-pro/%'   ");
		$this->loadUrlsByLocation("http://proadvisorservice.intuit.com/v1/search?latitude=%LAT%&longitude=%LON%&radius=100&pageNumber=&pageSize=5000",null,5000);	
		
//		$this->loadUrl("http://search.demandforce.com/businesses?q=Dentists&p=1&lc=37.77494226194981%2C-122.41941550000001&ls=0.17367689898779304%2C0.2197265625&lz=11");
		
		//db::query("UPDATE raw_data set parsed = 0 where type='provisor_intuit' and parsed = 1 and url like '%proadvisor.intuit.com/quickbooks-pro/%'   ");

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

		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$kvp = new KeyValue_Parser();	
		$urls = array();
		log::info($url);
		//parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		
		$links = array();
		$data = array();
		if (preg_match("#proadvisorservice.intuit.com/v1/search#",$url))
		{
			$json_data = json_decode($html,true);

			$links = array();
			foreach ($json_data['searchResults'] as $data)
			{
				if (!isset($data['searchId']))continue;
				unset($data['id']);//redbean don't liket this
				$links[] =  "http://proadvisor.intuit.com/quickbooks-pro/".$data['searchId'];
				$data['SEARCH_ID']=$data['searchId'];

				$data['INDUSTRIES'] = 'ERROR';
				$data['SOURCE_URL'] = 'ERROR';
				$data['ACCOUNTING'] = 'ERROR';
				
			//	print_r($data);
				db::store($type,$data,array('SEARCH_ID'));	

			}
			
			if (!empty($links))
				$thiz->loadUrlsByArray($links);
		}
		else
		{
			// grab the searchid and load from the db...
			$data[] = $searchId = trim(basename($url));

			$x =  new  XPath($html);	
			$a = array();
			foreach($x->query("//label[contains(text(),'Accounting')]//following-sibling::ul//li") as $node)
			{
				$a[] = self::removespaces($node->textContent);
			}
			$data[] = $ACCOUNTING  = db::quote(join(", ",$a));

			$i = array();
			foreach($x->query("//label[@class='ng-binding' and contains(text(),'Industries')]//following-sibling::ul//li") as $node)
			{
				$i[] = self::removespaces($node->textContent);
			}
			$data[] = $INDUSTRIES  = db::quote(join(", ",$i));

			$data[] = $SOURCE_URL = db::quote($url);

			db::query("UPDATE $type set SOURCE_URL = '$SOURCE_URL', INDUSTRIES = '$INDUSTRIES', ACCOUNTING='$ACCOUNTING' WHERE SEARCH_ID = '$searchId'");
		}
	}
}

$r= new provisor_intuit();
$r->parseCommandLine();

