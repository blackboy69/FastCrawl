<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class progressiveagent extends baseScrape
{
    public static $_this=null;
	var $numberOfLoads = 0;

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
		//$this->proxy = "localhost:9666";78413

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		$this->timeout=10;
//		$this->numRetries = 2;delete from raw_data where LENGTH(html) < 10000
		
		//
	/*			
		delete from load_queue where url in (select url from raw_data where LENGTH(html) < 10000 or length(HTML) between 78300 and 78415)
		delete from raw_data where LENGTH(html) < 10000 or length(HTML) between 78300 and 78415

		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='progressiveagent' and parsed = 1 ");
	*/
	//	db::query("	DROP TABLE progressiveagent"); 
		//db::query("UPDATE raw_data set parsed = 0 where type='progressiveagent' and parsed = 1 ");
		$this->loadUrlsByZip("http://www.progressiveagent.com/findanagent/search-results.aspx?product=Auto&zipCode=%ZIP%");
		$this->switchProxy();
	}
	
	var $count =0;
	static function loadCallBack($url,$html,$arg3)
	{
		$type = get_class();	
		$host = parse_url($url,PHP_URL_HOST);
		$thiz = self::getInstance();

		static $numberOfLoads = 0;
		
		if (strlen($html) < 10000 )
			$html = "";

		if (strlen($html) > 78300 && strlen($html)  < 78415)
			$html = "";
		
		if ($numberOfLoads++ > 500)
		{
			$thiz->switchProxy();
			$numberOfLoads=0;
		}
		baseScrape::loadCallBack($url,$html,$arg3);

		sleep(1);
	}


	public static function parse($url,$html)
	{

		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		if (preg_match("/search-results.aspx/",$url))
		{
			$urls = array();
			// load listings
			foreach ($x->query("//td[@class = 'containerColumn1Agent']//a[text()='View Agency Details & Map']") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"))."&aType=Agent";
			}
			foreach ($x->query("//td[@class = 'containerColumn2Broker']//a[text()='View Agency Details & Map']") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"))."&aType=Broker";
			}

			if (empty($urls))
			{
				foreach ($x->query("//a[text()='View Agency Details & Map']") as $node)
				{
					$urls[] = self::relative2absolute($url,$node->getAttribute("href"))."&aType=Agent";
				}
			}
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$data = array();
			foreach ($x->query("//p[@itemprop='name']") as $node)
			{
				$data['NAME'] =$node->textContent;
			}

			foreach ($x->query("//span[@itemprop='address']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
			}

			foreach ($x->query("//span[@itemprop='telephone']") as $node)
			{
				$data['PHONE'] = $node->textContent;
			}
			foreach ($x->query("//span[@itemprop='url']") as $node)
			{
				$data['WEBSITE'] = $node->getAttribute("href");
			}
			
			parse_str(parse_url($url,PHP_URL_QUERY),$q);

			$data['TYPE']= $q['aType'];

			if (!empty($data['NAME']))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data['NAME']);		
				db::store($type,$data,array('NAME','PHONE','ZIP'));	
			}
		
		}

	

	}
}

$r= new progressiveagent();
$r->parseCommandLine();

