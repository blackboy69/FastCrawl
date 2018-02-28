<?
include_once "config.inc";

class facebook_vet extends baseScrape
{
    public static $_this=null;
	public static $google = null;
	public static $bing = null;	
	public static $yahoo = null;
	public static $csv=null;
	public static $csvMap=array();

	private $urlsToLoad = array();
	function __construct()
	{
		parent::__construct();
		self::$bing = new search_engine_bing();
				self::$yahoo = new search_engine_yahoo();

		//$this->debug=true;
		//	log::$errorLevel = ERROR_ALL;

		log::info("Loading CSV");
		self::$csv = arrayFromCSV("Vet.csv",$hasFieldNames = true);

		$i =0;
		foreach(self::$csv as $row)
		{
			$term = "site:facebook.com/pages " . $row['Company'] ." ".$row['City'] .", ".$row['State']  ;
		//	$url = self::$bing->url($term);
			$url = self::$yahoo->url($term);
			self::$csvMap[$url] = $i;
			$this->urlsToLoad[] = $url;

			$i++;
		}
		log::info("Done Loading CSV");
$this->threads=2;
//		R::freeze();
	}

	
   public function runLoader()
   {
		
		$type = get_class();		
		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
	
		//$this->proxy = "localhost:9666";
		// load the csv

		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
	
*/
		$this->loadUrlsByArray($this->urlsToLoad);
   }

	
	static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$x = new HtmlParser($html);	
		$op = new Operating_Hours_Parser();
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();
$host = parse_url($url,PHP_URL_HOST);
//log::info(" parse($url $host)");
		$host = parse_url($url,PHP_URL_HOST);
		if (preg_match("/yahoo/",$host))
		{
			$csvid = self::$csvMap[$url];		
			$row = self::$csv[$csvid];

			$yahooResultUrls = self::$yahoo->parse($html);

			if (sizeof($yahooResultUrls)>0)
			{
				log::info("> QUEUED   {$row['Company']}");

				$fb = self::normalizeFacebookLink($yahooResultUrls[1]);
//				$fb = self::normalizeFacebookLink($yahooResultUrls[2]);

				log::info("loading $fb");
				 self::getInstance()->loadUrl($fb);	
			}
			
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

	static function normalizeFacebookLink($url)
	{		
		$u = parse_url($url);
		$toLoad = $u['scheme']."://".$u['host'].$u['path'];
		return $toLoad."?sk=info&_fb_noscript=1";	
	}


	static function saveFacebookInfo($url,$html)
	{
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$op = new Operating_Hours_Parser();
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();

		$data=array();            
		

		foreach ($x->query("//span[contains(@class,'ginormousProfileName')]") as $node)
		{
			$data['name']  = $node->textContent;
		}
		if (empty($data['name']))
		{
			foreach ($x->query("//div[contains(@class,'name')]") as $node)
			{
				$data['name']  = preg_replacE("/AboutTimeline.+/","", $node->textContent);
				$data['fans']  = "Unknown";
			}
		}

		foreach ($x->query("//span[@class='uiNumberGiant fsxxl fwb']") as $node)
		{
			$data['fans']  = $node->textContent;
		}

		foreach ($x->query("//div[@class='phs']//tr") as $node)
		{
			
			$x2 = new XPath($node);			

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
		foreach ($x->query("//*[contains(@class,'businessAddress']") as $node)
		{
			$newData  = $ap->parse($node->textContent);

			if (isset($newData['CITY']))
			{
				$data = array_merge($data, $newData);
				break;
			}
		}

		if (array_key_exists("name", $data))
		{
			$allowedFields = array('CONTACT_NAME','CSV_COMPANY','PRIMARY_ADDRESS','CITY','STATE','PRIMARY_ZIP_CODE','FACEBOOK_URL','FACEBOOK_NAME','FACEBOOK_FANS','FACEBOOK_LOCATION','FACEBOOK_HOURS','FACEBOOK_ABOUT','FACEBOOK_PARKING','FACEBOOK_PHONE','FACEBOOK_ADDRESS','FACEBOOK_WEBSITE','FACEBOOK_STATUS','FACEBOOK_GENERAL_INFORMATION','FACEBOOK_EMAIL','FACEBOOK_DESCRIPTION','FACEBOOK_FOUNDED','FACEBOOK_MISSION','FACEBOOK_AWARDS','FACEBOOK_PRODUCTS','FACEBOOK_LIKES','FACEBOOK_PUBLIC_TRANSIT','FACEBOOK_MAP','FACEBOOK_FOOD_STYLES','FACEBOOK_ATTIRE','FACEBOOK_PAYMENT_OPTIONS','FACEBOOK_SERVICES','FACEBOOK_SPECIALTIES','FACEBOOK_CSV_COMPANY_OVERVIEW','FACEBOOK_OTHER','FACEBOOK_FAVORITE_SPORTS','FACEBOOK_FAVORITE_TEAMS','FACEBOOK_FACEBOOK','FACEBOOK_E_MAIL','FACEBOOK_AFFILIATION','FACEBOOK_ADRESSE','FACEBOOK_SITE_WEB','FACEBOOK_ACTIVITIES','FACEBOOK_MUSIC','FACEBOOK_HOURS__ESTIMATED_','ADDRESS','HOURS','STATUS','WEBSITE','PHONE','CSV_CSV_COMPANY','CSV_CITY','CSV_STATE' );

			foreach($data as $k =>$v)
			{
				$k = trim($k);
				$v = trim($v);

				$compressedKey = preg_replace("/[^a-zA-Z0-9]/","",$k);
				if (strlen($compressedKey) <3)
					break;
				$k = strtoupper(preg_replace("/[^A-Za-z0-9]/","_","FACEBOOK $k"));
				if (in_array($k, $allowedFields))
				{
					$newData[$k]=$v;
				}
			}

			$data = $newData;
			$data['XID'] = sha1($url);
			$data['SOURCE_URL'] = $url;
			db::store($type,$data,array('XID'));
/*
			parse_str(parse_url($url,PHP_URL_QUERY));
			if (isset($csvid) && $csvid!="")
			{
				// try to merge against the csv file.
				$vetDetailsFromCsv = self::$csv[$csvid];	
				
				$newData = array();
				$newData['CSV_COMPANY'] 	 = $vetDetailsFromCsv['Company'];
				$newData['CSV_CITY']	 = $vetDetailsFromCsv['City'];
				$newData['CSV_STATE']	 = $vetDetailsFromCsv['State'];
				
				$allowedFields = array('CONTACT_NAME','CSV_COMPANY','PRIMARY_ADDRESS','CITY','STATE','PRIMARY_ZIP_CODE','FACEBOOK_URL','FACEBOOK_NAME','FACEBOOK_FANS','FACEBOOK_LOCATION','FACEBOOK_HOURS','FACEBOOK_ABOUT','FACEBOOK_PARKING','FACEBOOK_PHONE','FACEBOOK_ADDRESS','FACEBOOK_WEBSITE','FACEBOOK_STATUS','FACEBOOK_GENERAL_INFORMATION','FACEBOOK_EMAIL','FACEBOOK_DESCRIPTION','FACEBOOK_FOUNDED','FACEBOOK_MISSION','FACEBOOK_AWARDS','FACEBOOK_PRODUCTS','FACEBOOK_LIKES','FACEBOOK_PUBLIC_TRANSIT','FACEBOOK_MAP','FACEBOOK_FOOD_STYLES','FACEBOOK_ATTIRE','FACEBOOK_PAYMENT_OPTIONS','FACEBOOK_SERVICES','FACEBOOK_SPECIALTIES','FACEBOOK_CSV_COMPANY_OVERVIEW','FACEBOOK_OTHER','FACEBOOK_FAVORITE_SPORTS','FACEBOOK_FAVORITE_TEAMS','FACEBOOK_FACEBOOK','FACEBOOK_E_MAIL','FACEBOOK_AFFILIATION','FACEBOOK_ADRESSE','FACEBOOK_SITE_WEB','FACEBOOK_ACTIVITIES','FACEBOOK_MUSIC','FACEBOOK_HOURS__ESTIMATED_','ADDRESS','HOURS','STATUS','WEBSITE','PHONE','CSV_CSV_COMPANY','CSV_CITY','CSV_STATE' );

				foreach($data as $k =>$v)
				{
					$k = trim($k);
					$v = trim($v);

					$compressedKey = preg_replace("/[^a-zA-Z0-9]/","",$k);
					if (strlen($compressedKey) <3)
						break;
					$k = strtoupper(preg_replace("/[^A-Za-z0-9]/","_","FACEBOOK $k"));
					if (in_array($k, $allowedFields))
					{
						$newData[$k]=$v;
					}
				}

				$data = $newData;
				//$data = array();
				//$data = array_merge($vetDetailsFromCsv,$newData);
				
				

				$match = false;
				$matchCount = 0;
				// there should be at least one word in common
				foreach (explode(" ",$data['CSV_COMPANY']) as $word1)
				{
					foreach(explode(" ",$data['FACEBOOK_NAME']) as $word2)
					{
						if (strtoupper(trim($word1)) ==(strtoupper(trim($word2))))
						{
							$matchCount++;
						}
					}
				}

				// need a tighter match.
				if ($matchCount >= sizeof(explode(" ",$data['CSV_COMPANY'])) ||
					 $matchCount >= sizeof(explode(" ",$data['FACEBOOK_NAME'])))
				{
					$match= true;
				}


				
				if ($match)
				{
					log::info("+ MATCHED  {$data['CSV_COMPANY']} ==> {$data['FACEBOOK_NAME']}");
					
					try {				
						db::store($type,$data,array('CSV_COMPANY', 'CSV_CITY', 'CSV_STATE'));
					}
					catch(Exception $e)
					{
						log::error ("Cannot store ".$data['FACEBOOK_name']);
						log::error($e);
						//print_r($data);
						exit;
					}		
				}
				else
				{
					log::info("- NO MATCH {$data['CSV_COMPANY']} !!!! {$data['FACEBOOK_NAME']}");
				}

			}
			else
			{
					log::error ("Cannot match !" . $data['FACEBOOK_NAME']);
					log::error($url);
					#print_r($data);
				
			}
*/
		}
	}
}
$r = new facebook_vet();
$r->parseCommandLine();
