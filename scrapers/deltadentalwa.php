<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class deltadentalwa extends baseScrape
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
		$this->threads=5;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='deltadentalwa' and LENGTH(html) < 3000)
			 AND type ='deltadentalwa'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='deltadentalwa'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='deltadentalwa')
				  AND processing = 0
			     AND type ='deltadentalwa'
		    )
		
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		*/
		// cananda top 100 cities by population
		
		
		//$this->noProxy=false;
		//$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		$urls = array();
		for( $i=1 ; $i < 20000 ; $i++ )
		{
			$urls[] = "http://www.deltadentalwa.com/Patient/Public/FindADentist/DentistDetails.aspx?DentistID=$i",$state)	;
		}
		$this->loadUrlsByArray($urls)
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

			
		$ap = new Address_Parser();
		$op = new Operating_Hours_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();


		$data = array();

		foreach ($x->query("//span[contains(@id,'lbltxtProviderName')]") as $node)
		{
			$data['COMPANY'] = trim($node->textContent);
		}

		foreach ($x->query("//span[contains(@id,'lblPhoneNo')]") as $node)
		{
			$data = array_merge($data, $pp->parse($node->textContent));
		}

		foreach ($x->query("//a[contains(@id,'hypDentistWebSite')]") as $node)
		{
			$href = trim($node->getAttribute("href"));
			$data["WEBSITE"] = $href;
		}

		$address[] = array();
		foreach ($x->query("//span[contains(@id,'lblAddressLine')]") as $node)
		{
			$address[] = trim($node->textContent);
		}
		$data = array_merge($data, $ap->parse($address));

		foreach ($x->query("//span[@itemprop='reviewCount']") as $node)
		{
			$data['NUM_REVIEWS'] = trim($node->textContent);
		}

		// pull category
		$categories=array();
		foreach ($x->query("//p[@id='bizCategories']//span[@itemprop='title']") as $node)
		{
			$categories[] = trim($node->textContent);
		}
		$data['CATEGORIES'] = join(",", $categories);

		//print_R($data);;
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


			log::info("{$data['CITY']}, {$data['STATE']},  {$data['COMPANY']}");		
			db::store($type,$data,array('SOURCE_URL'));	
		}		
		
	
	}
}

$r= new deltadentalwa();
$r->parseCommandLine();

