<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class avvo extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

//		$this->maxRetries = 100;
	//	$this->timeout = 15;
		//$this->useCookies=false;
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=2;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='avvo' and LENGTH(html) < 3000)
			 AND type ='avvo'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='avvo'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='avvo')
				  AND processing = 0
			     AND type ='avvo'
		    )
		
			 ");
		
		*/
		// cananda top 100 cities by population
		//db::query("UPDATE raw_data set parsed = 0 where type='avvo' and parsed = 1  ");
		
		$this->noProxy=true;
		$this->switchProxy(null,true);

		$urls = array();
		foreach(array("ak", "al", "ar", "az", "ca", "co", "ct", "dc", "de", "fl", "ga", "hi", "ia", "id", "il", "in", "ks", "ky", "la", "ma", "md", "me", "mi", "mn", "mo", "ms", "mt", "nc", "nd", "ne", "nh", "nj", "nm", "nv", "ny", "oh", "ok", "or", "pa", "ri", "sc", "sd", "tn", "tx", "ut", "va", "vt", "wa", "wi", "wv", "wy") as $state)
		{
			// do it manually becuase need to be lowercase.
			$urls[] = "http://www.avvo.com/find-a-lawyer/all-practice-areas/$state";
		}
		if (!empty($urls))
			$this->loadUrlsByArray($urls);	
	}

	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		$host = parse_url($url,PHP_URL_HOST);

		/*
		if (strpos($html,"Sorry, you're not allowed to access this page."))
		{			
			log::info("Sorry, you're not allowed to access this page.");					
			$html=null;
		}*/

		if (strlen($html)<1000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
		sleep(1);
	}



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	


		if (strpos($url,"/all-practice-areas/"))
		{
			// get links to listing pages links
			$urls = array();
			foreach($x->query("//table[@class='links']//a[contains(@href,'-lawyer')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			if (!empty($urls))
				$thiz->loadUrlsByArray($urls);	
		}
		else if (strpos($url,"-lawyer/")) // listing page?
		{
			$urls = array();

			// get next links
			foreach($x->query("//ol[contains(@class,'result-list')]//a[contains(@href,'/attorneys/')]") as $node)
			{
				if ( preg_match("/[0-9]+\.html/",$node->getAttribute("href")) )
					$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// get next links
			foreach($x->query("//div[@id='bottom_pagination']//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			if (!empty($urls))
				$thiz->loadUrlsByArray($urls);	
		}
		else if (strpos($url,"/attorneys/")) // listing page? // profile page
		{
			
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();
			$np = new Name_Parser();

			$data = array();
			$profile_title="";
			$address = array();

			foreach ($x->query("//h1[@class='profile_title']") as $node)
			{
				$data = array_merge($data,$np->parse($node->textContent));
			}
			

			$data['COMPANY'] = "Not Provided";
			foreach ($x->query("//div[@class='address_name']") as $node)
			{
				$data['COMPANY'] = trim($node->textContent);
			}


			$phone=array();
			foreach ($x->query("//div[@class='address_phone']") as $node)
			{
				$phone[] = self::cleanup(str_replace("\n"," ", $node->textContent));
			}
			$data = array_merge($data,$kvp->parse($phone));

			
			foreach ($x->query("//div[contains(@class,'address_line_')]") as $node)
			{
				$address[] = trim($node->textContent);
			}			
			foreach ($x->query("//div[@class='address_city_state']") as $node)
			{
				$address[] = trim($node->textContent);
			}
			$data = array_merge($data,$ap->parse($address));

			foreach ($x->query("//td[@class='links']//p[@class='other_link additional_link']//a") as $node)
			{
				$data["WEBSITE"] = trim($node->getAttribute("href"));
			}
			
			$practice_areas = array();
			foreach ($x->query("//li[@class='specialty']") as $node)
			{
				$textContent = trim($node->textContent);
				$end = strpos($textContent,"  ");
				if ($end > 0)
					$practice_areas[] = substr($textContent, 0, $end);
				else
					$practice_areas[] = $textContent;
			}
			$data["AREAS_OF_PRACTICE"] = join(", ", $practice_areas);
			
			foreach ($x->query("//img[@class='avvo_pro_badge']") as $node)
			{
				$data['AVVO_PRO_ACCOUNT'] = "YES";
			}

			foreach ($x->query("//div[@class='client_review_star_rating']/a[@class='count']/strong") as $node)
			{
				$data['NUM_REVIEWS'] = trim($node->textContent);
			}

			foreach ($x->query("//div[@class='client_review_star_rating']/a[@class='star_rating']/img") as $node)
			{
				$data['RATING'] = trim($node->getAttribute("alt"));
			}

			//print_R($data);;
			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;
				unset ($data['RAW_ADDRESS']);
				if (empty($data['FIRST_NAME']))
					return;

				ECHO(".");						
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			
		}
	}
}

$r= new avvo();
$r->parseCommandLine();

