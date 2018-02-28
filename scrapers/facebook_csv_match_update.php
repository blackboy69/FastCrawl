<?
include_once "config.inc";

class facebook_csv_match_update extends baseScrape
{
    public static $_this=null;
	public static $google = null;
	public static $bing = null;	
	public static $csv=null;
	public static $csvMap=array();

	private $urlsToLoad = array();
	function __construct()
	{
		parent::__construct();
		self::$bing = new search_engine_bing();

		//$this->debug=true;
		//	log::$errorLevel = ERROR_ALL;

		// load the csv
		log::info("Loading CSV");
		self::$csv = arrayFromCSV("Vet list_Byron Facebook scrape.csv",$hasFieldNames = true);

		$i =0;
		foreach(self::$csv as $row)
		{
			$term = "site:facebook.com \"" . $row['COMPANY_NAME'] ."\" ".$row['PRIMARY_CITY'] .", ".$row['PRIMARY_STATE']  ;
			$url = self::$bing->url($term);
			self::$csvMap[$url] = $i;
			$this->urlsToLoad[] = $url;

			$i++;
		}
		log::info("Done Loading CSV");

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
		
*/
		//$this->proxy = "localhost:9666";
		$this->noProxy=true;
		$this->nextProxyUrl = "http://hidemyass.com/proxy-list/search-225371"; // USA ONLY. 
		$this->threads=8;
		$this->useCookies = false;
		$this->timeout = 5;
	

		$this->loadUrlsByArray($this->urlsToLoad);
   }

	
	static function parse($url,$html)
	{
		//log::info(" parse($url)");
		$host = parse_url($url,PHP_URL_HOST);
		if (preg_match("/bing/",$host))
		{
			$csvid = self::$csvMap[$url];		
			$row = self::$csv[$csvid];

			$bingResultUrls = self::$bing->parse($html);
			if (sizeof($bingResultUrls)>0)
			{
				log::info("> QUEUED   {$row['COMPANY_NAME']}");

				$fb = self::normalizeFacebookLink($bingResultUrls[0])."&csvid=$csvid";
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
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$data=array();            
		$data['url'] = $url;

		foreach ($x->query("//span[contains(@class,'ginormousProfileName')]") as $node)
		{
			$data['name']  = $node->textContent;
		}

		foreach ($x->query("//span[@class='uiNumberGiant fsxxl fwb']") as $node)
		{
			$data['fans']  = $node->textContent;
		}

		foreach ($x->query("//div[@class='phs']//tr") as $node)
		{
			
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	
			

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

		if (array_key_exists("name", $data))
		{
			parse_str(parse_url($url,PHP_URL_QUERY));
			if (isset($csvid) && $csvid!="")
			{
				// try to merge against the csv file.
				$vetDetailsFromCsv = self::$csv[$csvid];	
				
				$newData = array();
				$newData['CONTACT_NAME'] 	 = $vetDetailsFromCsv['CONTACT_NAME'];
				$newData['COMPANY_NAME'] 	 = $vetDetailsFromCsv['COMPANY_NAME'];
				$newData['PRIMARY_ADDRESS']  = $vetDetailsFromCsv['PRIMARY_ADDRESS'];
				$newData['PRIMARY_CITY']	 = $vetDetailsFromCsv['PRIMARY_CITY'];
				$newData['PRIMARY_STATE']	 = $vetDetailsFromCsv['PRIMARY_STATE'];
				$newData['PRIMARY_ZIP_CODE'] = $vetDetailsFromCsv['PRIMARY_ZIP_CODE'];
				
				$allowedFields = array('CONTACT_NAME','COMPANY_NAME','PRIMARY_ADDRESS','PRIMARY_CITY','PRIMARY_STATE','PRIMARY_ZIP_CODE','FACEBOOK_URL','FACEBOOK_NAME','FACEBOOK_FANS','FACEBOOK_LOCATION','FACEBOOK_HOURS','FACEBOOK_ABOUT','FACEBOOK_PARKING','FACEBOOK_PHONE','FACEBOOK_ADDRESS','FACEBOOK_WEBSITE','FACEBOOK_STATUS','FACEBOOK_GENERAL_INFORMATION','FACEBOOK_EMAIL','FACEBOOK_DESCRIPTION','FACEBOOK_FOUNDED','FACEBOOK_MISSION','FACEBOOK_AWARDS','FACEBOOK_PRODUCTS','FACEBOOK_LIKES','FACEBOOK_PUBLIC_TRANSIT','FACEBOOK_MAP','FACEBOOK_FOOD_STYLES','FACEBOOK_ATTIRE','FACEBOOK_PAYMENT_OPTIONS','FACEBOOK_SERVICES','FACEBOOK_SPECIALTIES','FACEBOOK_COMPANY_OVERVIEW','FACEBOOK_OTHER','FACEBOOK_FAVORITE_SPORTS','FACEBOOK_FAVORITE_TEAMS','FACEBOOK_FACEBOOK','FACEBOOK_E_MAIL','FACEBOOK_AFFILIATION','FACEBOOK_ADRESSE','FACEBOOK_SITE_WEB','FACEBOOK_ACTIVITIES','FACEBOOK_MUSIC','FACEBOOK_HOURS__ESTIMATED_');

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
				// there should be at least one word in common
				foreach (explode(" ",$data['COMPANY_NAME']) as $word1)
				{
					foreach(explode(" ",$data['FACEBOOK_NAME']) as $word2)
					{
						if (strtoupper(trim($word1)) ==(strtoupper(trim($word2))))
						{
							$match = true;
						}
					}
				}
				
				if ($match)
				{
					log::info("+ MATCHED  {$data['COMPANY_NAME']} ==> {$data['FACEBOOK_NAME']}");
					
					try {				
						db::store($type,$data,array('COMPANY_NAME', 'PRIMARY_CITY', 'PRIMARY_STATE'));
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
					log::info("- NO MATCH {$data['COMPANY_NAME']} !!!! {$data['FACEBOOK_NAME']}");
				}

			}
			else
			{
					log::error ("Cannot match !" . $data['FACEBOOK_NAME']);
					log::error($url);
					#print_r($data);
				
			}

		}
	}
}
$r = new facebook_csv_match_update();
$r->parseCommandLine();
