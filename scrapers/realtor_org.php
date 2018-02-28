<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class realtor_org extends baseScrape
{
   public function runLoader()
   {
      $type = get_class();    

      $this->maxRetries = 100; // switch to a different url if it returns no data> this many times
      $this->timeout = 5;
      $this->useCookies=true;
      $this->allowRedirects = true;
      $this->debug=false;
		$this->threads=1;
		$this->noProxy=false;
		$this->proxy = 'localhost:9666';
		//$this->switchProxy(null,true);

		db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1 and url not like 'http://community.spiceworks.com%' ");
		db::query("DROP TABLE $type");	

///				SELECT zip FROM geo.locations where zip > 04500 
		  $result = mysql_query("SELECT 
					geo.locations.zip  as ZIP 
				FROM  geo.us_cities 
				INNER JOIN geo.locations ON (geo.us_cities.city = geo.locations.city AND geo.US_CITIES.STATE  = GEO.locations.state)
				GROUP BY geo.us_cities.city, geo.us_cities.state , geo.locations.zip
				ORDER BY geo.us_cities.POP DESC
			");

		while ($r = mysql_fetch_row($result))
      {
			$zip = sprintf("%05d", $r[0]);
	///	$zip=95403;

			$urlToPost = "http://nrdssearch.realtor.org/NrdsSearch/app/servlet/NrdsSearchDetail?zip=$zip";
			$this->setReferer($urlToPost, "http://nrdssearch.realtor.org/NrdsSearch/app/servlet/NrdsSearch?action=homeOffice");
			
			$webRequests = new WebRequest($urlToPost,$type, "POST", "action=searchOffice&officeName=&drLastName=&drFirstName=&city=&state=&zip=$zip");        
			
			
			$this->loadWebRequests(array($webRequests));
			
			//serialize this one.
			$this->parseData();
			echo ".";
      }

	}


	// break the captcha
   static function loadCallBack($url,$html,$arg3)
   {
		log::info("Loaded: $url");
      if (empty($url)) //timeout?
         return;

      $thiz = self::getInstance();

		if ($thiz->isBanned($url,$html,$arg3))
			return;

	
		$count=0;
		while (strpos($html,"By entering the code you see in the box, you help us prevent automated programs from using this application."))
		{
			$count++;
			$x = new Xpath($html);

			$thiz->setReferer("http://nrdssearch.realtor.org/NrdsSearch/app/captcha-image.html", $url);
			$img = $thiz->Get("http://nrdssearch.realtor.org/NrdsSearch/app/captcha-image.html");
	
			$tmpFile = tempnam(sys_get_temp_dir(), 'png');			

			file_put_contents($tmpFile, $img);
			$cp = new Captcha_parser();
			$j_captcha = $cp->parse($tmpFile);
	
			unlink($tmpFile); // this removes the file

			log::info("Solved Captcha: j_captcha_response=$j_captcha");
			$thiz->setReferer($url, $url);
			$html = $thiz->Post($url, "j_captcha_response=$j_captcha");

			// don't try to solve more than 5 times....
			if ($count>10)
			{
				log::info("Too many retries");
				$html = null;
				return baseScrape::loadCallBack($url,$html,$arg3);
			}
		}

		if ($thiz->isBanned($url,$html,$arg3))
			return;

//			=== now parse the page listings but don't store the metadata html
		$x = new Xpath($html);

		foreach($x->query("//a[contains(@href,'getOffice')]") as $node)
		{
			$toLoad =self::relative2Absolute($url,$node->getAttribute("href"));
			if ($toLoad != "")
			{
				$thiz->setReferer($toLoad,$url);

				// did it work?
				$html = $thiz->Get($toLoad);
				if ($thiz->isBanned($url,$html,$arg3))					
					return;

				
				$thiz->parsePage($url,$html);
			}
		}
		
   }
	
	function isBanned($url,$html,$arg3)
	{
		if (empty($html))
		{
			log::info("Got empty html!");
			baseScrape::loadCallBack($url,$html,$arg3);
			return true;
		}


		if (strpos($html,"Server is temporarily unavailable. Please try again later.")>-1 ||
			 strpos($html,"Access Denied.")>-1 ||
			 strpos($html,"To keep you safe from internet threats, please log in to your company's ")>-1
			)
			
      {
         log::info("Time to Switch IP: Server is temporarily unavailable. Please try again later.");               
         baseScrape::loadCallBack($url,$html,$arg3);
			$this->switchProxy(null,true);
			return true;
      }
		return false;
	}

	function parse($url,$html){
		log::info("Parsed " . strlen($html) . " bytes in $url ");
	}

	function parsePage($url,$html)
	{

		$type = get_class();    
      $thiz = self::getInstance();

		$x = new Xpath($html);
		$data = array();
		log::info($url);
		foreach($x->query("//tr") as $node)
		{
			$x2=new Xpath($node);
			$key = $val  = "";
			foreach($x2->query("//th") as $node2)
			{
				$key = self::cleanup($node2->textContent);
			}
			foreach($x2->query("//td") as $node2)
			{
				$val = self::cleanup($node2->textContent);
			}
			
			if (!empty($key))
				$data[$key]= $val;

		}
		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('OFFICE_ID')); 
		}
		else
		{
			log::info("No data found?");
			log::info($html);
			//exit;
		}
		
	}

}

$r= new realtor_org();
$r->parseCommandLine();

