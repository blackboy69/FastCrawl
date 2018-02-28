<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class patientactivator extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

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
			 url IN (SELECT url FROM raw_data WHERE type ='patientactivator' and LENGTH(html) < 3000)
			 AND type ='patientactivator'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='patientactivator'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='patientactivator')
				  AND processing = 0
			     AND type ='patientactivator'
		    )
		
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		*/
		// cananda top 100 cities by population
		//db::query("UPDATE raw_data set parsed = 0 where type='patientactivator' and parsed = 1 and url not like '%?%'  ");
		#db::query("UPDATE raw_data set parsed = 0 where type='patientactivator' and parsed = 1 and url not like '%?%'  ");
		#		db::query("DROP TABLE $type");

		$this->loadUrl("http://www.1800dentist.com/find-a-dentist/");
		/*
		$this->noProxy=false;
		$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		foreach(array("WA","AK") as $state)
		{
			$this->loadUrlsByCity("http://www.yelp.com/search?find_desc=Dentist&find_loc=%CITY%,%STATE%&ns=1&rpp=40",$state)	;
		}*/

	}

	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		if (strpos($html,"Sorry, you're not allowed to access this page."))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Sorry, you're not allowed to access this page.");
					
			$html=null;
		}

		if (strlen($html)<5000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
		//sleep(1);
	}



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

		// grab the state links
		foreach($x->query("//a[contains(@id,'lnkState')]") as $node) 
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}

		// grab the city links
		foreach($x->query("//a[contains(@id,'lnkCity')]") as $node) 
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		
		// Member Dentists
		foreach($x->query("//a[contains(@id,'lnkmemberdentists')]") as $node) 
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		// link to user
		foreach($x->query("//a[contains(@id,'lnkmemberdentist')]") as $node) 
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}

		// Member Dental Practice Groups 
		foreach($x->query("//a[contains(@id,'lnkmemberoffices')]") as $node) 
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		// link to user
		foreach($x->query("//a[contains(@id,'lnkoffices')]") as $node) 
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}

		// Other Non-member Dentists
		foreach($x->query("//a[contains(@id,'lnkothermembers')]") as $node) 
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}

		foreach($x->query("//a[contains(@id,'lnkmemberoffice')]") as $node) 
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}

		foreach($x->query("//a[contains(@id,'lnkDentist')]") as $node) 
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}

		
		
		if (!empty($urls))
		{
			$thiz->loadUrlsByArray($urls);	
			return;
		}


					
		
		$ap = new Address_Parser();
		$op = new Operating_Hours_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();

		$data = array();
		$name = "";

		foreach ($x->query("//span[contains(@id,'lblCompany')]") as $node)
		{
			$name = trim($node->textContent);
		}

		if (empty($name))
		{
			foreach ($x->query("//div[@class='addressPan']//h1") as $node)
			{
				$name = trim($node->textContent);
			}
		}

		if (preg_match("/DENTAL|GROUP|PRACTICE|FIRM/i", $name))
			$data['COMPANY'] = $name;
		else 
			$data = array_merge($data, $np->parse($name) );


		foreach ($x->query("//div[@class='addressPan']//span") as $node)
		{
			$data['PHONE'] = trim($node->textContent);
		}

		/*
		foreach ($x->query("//div[@id='bizUrl']//a") as $node)
		{
			$href = trim($node->getAttribute("href"));
			$data["WEBSITE"] = urldecode($thiz->urlVar($href,"url"));
		}*/

		foreach ($x->query("//div[@class='addressPan']//p") as $node)
		{
			$data = array_merge($data, $ap->parse($node->c14n()));
		}
		foreach ($x->query("//span[@id='ctl00_cphMain_lblReviews2']") as $node)
		{
			$data['NUM_REVIEWS'] = trim($node->textContent);
		}


		foreach ($x->query("//meta[@itemprop='ratingValue']") as $node)
		{
			$data['AVG_RATING'] = trim($node->getAttribute("content"));
		}
/*
		// pull category
		$categories=array();
		foreach ($x->query("//span[@itemprop='title']") as $node)
		{
			$categories[] = trim($node->textContent);
		}
		$data['CATEGORIES'] = join(",", $categories);*/
		
		//print_R($data);;
		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;


			if (empty($data['COMPANY']) && empty($data['FIRST_NAME']))
				return;
			
			$data['CATEGORIES'] =  $data["WEBSITE"] = "Not Provided";

			// Demandforce requirements
			if (empty($data['COMPANY']) )
				$data['COMPANY']="Not Provided";
			
			if (empty($data['FIRST_NAME']) )
				$data['FIRST_NAME']="Not Provided";

			if (empty($data['LAST_NAME']) )
				$data['LAST_NAME']="Not Provided";
			
			if (empty($data['PHONE']))
				$data['PHONE'] = 'Not Provided';
		
			if(preg_match("/ |[a-z]/", @$data['ZIP']))
				$data['COUNTRY'] = 'Canada';
			else
				$data['COUNTRY'] = 'United States';

			unset ($data['RAW_ADDRESS']);


			echo ".";	
			db::store($type,$data,array('SOURCE_URL'));	
		}
	}
}

$r= new patientactivator();
$r->parseCommandLine();

