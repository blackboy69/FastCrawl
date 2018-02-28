<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class mouthhealthy extends baseScrape
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
		//$this->threads=2;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
			/* db::query("
		delete from load_queue WHERE url like '%search-results.aspx%'
		and processing =0
			 ");
	
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='mouthhealthy' and LENGTH(html) < 3000)
			 AND type ='mouthhealthy'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='mouthhealthy'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='mouthhealthy')
				  AND processing = 0
			     AND type ='mouthhealthy'
		    )
		
			 ");
		
		*/




		// cananda top 100 cities by population



		//db::query("UPDATE raw_data set parsed = 0 where type='mouthhealthy' and parsed = 1 and url like 'http://www.mouthhealthy.org/en/find-a-dentist/dentist-profile.aspx%' ");
//		db::query("drop table $type ");
		
		//$this->noProxy=true;
		//$this->proxy="localhost:8888";
	//	$this->switchProxy(null,true);

		// INJECT COOKIES FROM FIDDLER EASIER THAN CODING IT!!!!
		$this->cookieData = 'Cookie: ASP.NET_SessionId=wbw2gvqufbtzf2xi0ess1kve; SC_ANALYTICS_SESSION_COOKIE=55C870C6BC8C43AC8E307439915915FD|1; SC_ANALYTICS_GLOBAL_COOKIE=862A44B1D9B64C00AD2AEC148EEEE92E; WT_FPC=id=99.187.228.12-434685088.30290172:lv=1365483816348:ss=1365483787946; __atuvc=2%7C15; websitemouthhealthy#lang=en; sc_pview_shuser=; Fad=accepted';


		$url ="http://www.mouthhealthy.org/en/find-a-dentist/search-results.aspx?rdAddress=%ZIP%&rdSpecialty=all%20types%20of%20dentistry&rdDistance=10&lastname=&photo=";
		//$this->loadUrlsByZip($url,100);	
	}


	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		$host = parse_url($url,PHP_URL_HOST);

		
		if (strpos($html,"Please accept the terms of use."))
		{			
			log::info("Please accept the terms of use.");					
			LOG::INFO("You need to grab a cookie: header from fiddler!");
			exit;
		}
		baseScrape::loadCallBack($url,$html,$arg3);

	}



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

		if (strpos($url,"/search-results.aspx"))
		{
			if (strpos($html,"No results were returned."))
			{
				log::info("No results were returned.");
				return;
			}

			// get links to listing pages links
			$urls = array();
			foreach($x->query("//a[contains(@href,'dentist-profile.aspx')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// get the next page links
			if (!empty($urls))
				$thiz->loadUrlsByArray($urls);	
		}
		else if (strpos($url,"/dentist-profile.aspx")) // listing page?
		{
			

			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();
			$np = new Name_Parser();

			$data = array();
			$profile_title="";
			$address = array();


			// FIRST / LAST NAME
			foreach ($x->query("//span[@id='phcontent_2_ctl00_labelName']") as $node)
			{
				$data = array_merge($data,$np->parse($node->textContent));
			}
			
			
			// COMPANY
			$data['COMPANY'] = "Not Provided";
			foreach ($x->query("//span[@id='phcontent_2_ctl00_lblpracticename']") as $node)
			{
				$data['COMPANY'] = self::cleanup($node->textContent);
			}
				
			// PHONE
			$phone=array();
			foreach ($x->query("//span[contains(@id,'phone')]") as $node)
			{
				$phone[] = self::cleanup(str_replace("\n"," ", $node->textContent));
			}
			$data = array_merge($data,$pp->parse($phone));
				
			// ADDRESS
			$address = array();
			foreach ($x->query("//span[@id='phcontent_2_ctl00_lblAddresses']") as $node)
			{
				$address = explode("Larger Map", str_replace("Office Location"," ", $node->c14n()));

				if (empty($address))
					$address = array($node->c14n());
			}
			$data = array_merge($data,$ap->parse($address[0]));

			//WEBSITE
			$data['WEBSITE'] = "Not Provided";
			foreach ($x->query("//span[@id='phcontent_2_ctl00_lblwebsite']") as $node)
			{
				$data['WEBSITE'] = self::cleanup($node->textContent);
			}

			//AREAS_OF_PRACTICE
			$practice_areas = array();
			foreach ($x->query("//span[@id='phcontent_2_ctl00_lblSpecialty']") as $node)
			{
				$textContent = self::cleanup(str_replace("Specialty","", $node->textContent));
				$end = strpos($textContent,"  ");
				if ($end > 0)
					$practice_areas[] = substr($textContent, 0, $end);
				else
					$practice_areas[] = $textContent;
			}
			$data["AREAS_OF_PRACTICE"] = join(", ", $practice_areas);
			
		

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

$r= new mouthhealthy();
$r->parseCommandLine();

