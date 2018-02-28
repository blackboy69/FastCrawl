<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class patientconnect365 extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		$this->maxRetries = 100;
		$this->timeout = 15;
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

		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		
		//db::query("UPDATE raw_data set parsed = 0 where type='patientconnect365' and parsed = 1   ");
//		$this->loadUrlsByZip("https://www.patientconnect365.com/Dentist?ZipCode=%ZIP%&State=%STATE%")	;

		//db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='patientconnect365' ");

      $result = mysql_query("SELECT distinct zip,state FROM geo.locations order by pop desc LIMIT 3000") ;
			
      while ($r = mysql_fetch_row($result))
      {
	//		$city = str_replace(" ","-", strtolower($r[0]));
//			$state = str_replace(" ","-", strtolower($r[1]));
			$zip = str_replace(" ","-", strtolower($r[0]));

			$url = "https://www.patientconnect365.com/Dentist/SearchDentistsJson?$zip";
			$webRequests[] = new WebRequest($url,$type,"POST","ZipCode=$zip");
        
      }
		$this->loadWebRequests($webRequests);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();

		$ap = new Address_Parser();
		$op = new Operating_Hours_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$x = new  XPath($html);	

		$data = array();
		
		if (preg_match("#/SearchDentistsJson#",$url))
		{
			$d = json_decode($html,true);

		//	print_r($d);
			// get next page links
			foreach($d as $k)
			{
				$urls[] = self::relative2absolute($url,$k["pu"]);
			}

			$thiz->loadUrlsByArray($urls);
			return;
		}

		foreach ($x->query("//h2[@itemprop='name']") as $node)
		{
			$data['COMPANY'] = trim($node->textContent);
		}
		foreach ($x->query("//span[@itemprop='telephone']") as $node)
		{
			$data['PHONE']=  trim($node->textContent);		
		}
	
		foreach ($x->query("//span[@class='url']//a") as $node)
		{
			$href = trim($node->getAttribute("href"));
			$data["WEBSITE"] =$href;
		}	
		
		foreach ($x->query("//strong[@itemprop='streetAddress']") as $node)
		{
			$data['ADDRESS']=  trim($node->textContent);		
		}
	
		foreach ($x->query("//span[@itemprop='addressLocality']") as $node)
		{
			$data['CITY']=  trim($node->textContent);		
		}
	
		foreach ($x->query("//span[@itemprop='addressRegion']") as $node)
		{
			$data['STATE']=  trim($node->textContent);		
		}
	
		foreach ($x->query("//span[@itemprop='postalCode']") as $node)
		{
			$data['ZIP']=  trim($node->textContent);		
		}
	
		foreach ($x->query("//meta[@itemprop='ratingValue']") as $node)
		{
			$data['RATING'] = trim($node->getAttribute("content"));
		}

		foreach ($x->query("//span[@itemprop='reviewCount']") as $node)
		{
			$data['NUM_REVIEWS'] = trim($node->textContent);
		}
		// pull category
		$categories=array();
		foreach ($x->query("//span[@itemprop='relevantSpecialty']") as $node)
		{
			$cat = trim($node->textContent);
			if (!empty($cat))
				$categories[] =  $cat ;
		}
		$data['CATEGORIES'] = join(",", $categories);

		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;

			// Demandforce requirements
			$data['FIRST_NAME']="Not Provided";
			$data['LAST_NAME']="Not Provided";
			if(preg_match("/ |[a-z]/", @$data['ZIP']))
				$data['COUNTRY'] = 'Canada';
			else
				$data['COUNTRY'] = 'United States';

			if (empty($data['PHONE']))
				return;
			
//				echo ".";		
			log::info($data);
			db::store($type,$data,array('COMPANY','PHONE','ADDRESS','ZIP', 'ACCOUNT_TYPE'));	
		}		
	}
}

$r= new patientconnect365();
$r->parseCommandLine();

