<?
include_once "config.inc";
include_once "search_engine_yahoo_local_scraper.php";

class netvu_yahoo extends baseScrape
{
    public static $_this=null;
	
	function __construct()
	{
		parent::__construct();
		$this->yahoo = new search_engine_yahoo_local_scraper();
	}


   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
		$this->switchProxy();
//		$this->proxy = "37.58.52.8:222";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		$this->maxRetries   = 5;
		$this->sleepInterval= 2; 
//		$this->retryEnabled=false;
//		db::query("DROP TABLE $type");	
	//	db::query("UPDATE raw_data set parsed = 0 where type='NETVU_YAHOO'");

		/*db::query("UPDATE raw_data set parsed = 0 where type='NETVU_YAHOO' and parsed = 1 ");
		db::query("DROP TABLE $type");
	
		
		



		db::query("DELETE FROM load_queue where type='NETVU_YAHOO' ");
		db::query("DELETE FROM raw_data where type='NETVU_YAHOO' ");

		

		$this->loadUrl($this->bing->url('This website is powered by LexisNexis® NETVU_YAHOO-Hubbell®'));	*/	
		//$this->loadUrlsByArray(self::allTheLinks());

				//*[@id="local"]/ol/li[1]/div/div[1]/h3
//		
//		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1 ");	
		//db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1  and url not in (select url from raw_data where length(html) > 5000) ");
		
		$urls = array();
		foreach(db::query("SELECT NAME,PROFILE_URL,COMPANY_NAME,FAX,STATE,ZIP,CITY,ADDRESS,SOURCE_URL,ADDRESS2,TOLLFREE,COUNTRY,HOME from netvu",$returnAllRows=true) as $row)
		{
			$urls[] =$this->yahoo->url($row['COMPANY_NAME'],"{$row['CITY']}, {$row['STATE']}");
		}
		$this->loadUrlsByArray($urls);
	}

	static $urlMap= array();
	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		static $numRequests = array();
		


		$thiz = self::getInstance();
		if (strpos($html,"Sorry, Unable to process request at this time"))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Sorry, you're not allowed to access this page.");

			$html=null;
		}

		if (strlen($html)<5000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);

	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$host = parse_url($url,PHP_URL_HOST);
		
		parse_str(parse_url($url,PHP_URL_QUERY),$q);


		$listings = $thiz->yahoo->parse($html,true,true);



		$data = db::query("SELECT * from netvu where COMPANY_NAME = '".db::quote($q['p'])."' ",$returnAllRows=false);
		unset($data['id']);

		log::info($url);

		foreach($listings as $listing)
		{
			//$lurl = $thiz->relative2absolute($url,$listing['URL']);
			$data["LOOKUP"] = $listing;
			$data["LOOKUP_URL"] = $url;
			
			if (isset($data["STATE"]))
			{
				$data['LOOKUP'] = array_merge($data['LOOKUP'],$ap->parse($data['LOOKUP']['RAW_ADDRESS'].", " .$data['STATE'].", " .$data['ZIP']));
				
				if (isset($data['LOOKUP']['TITLE']))
				{
					// if the titles are close then we are good
					similar_text ($data['LOOKUP']['TITLE'],$data['COMPANY_NAME'],$namePercent);
					similar_text ($data['LOOKUP']['CITY'],$data['CITY'],$cityPercent);
					$data['NAME_FUZZY_PERCENT_MATCH'] = $namePercent;
					$data['CITY_FUZZY_PERCENT_MATCH'] = $cityPercent;

					if ($namePercent > 60 && $cityPercent > 80)
					{
						log::info($data);
						db::store($type,$data , array('PROFILE_URL','LOOKUP_URL'));
					}
					else
					{
						log::info("Fuzzy match too low");
					}
				}
				else
				{
					log::info("Yahoo parse failed no title! ");
				}
			}
			else
			{
				log::info("Original data  no state! ");
			}	
		}
	}

}

		$r= new netvu_yahoo();

//		while(true)
		//{
			$r->parseCommandLine();
			echo "\nRUN THIS BITCH FOREVER\n\n";
//			sleep(10);

	///	}

