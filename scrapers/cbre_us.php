<?
include_once "config.inc";
//R::freeze();

class cbre_us_2017 extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		$this->timeout = 8000;// wait a really really long time.
		/*	
			
			$this->proxy = "localhost:8888";

			$this->useCookies=false;
			$this->allowRedirects = true;
			
			$this->debug=false;
		*/	
		//$this->clean();
		$this->threads=1;		
		// should be about 12,000 listings in the usa.
		//
		$this->loadUrl("http://www.cbre.us/_vti_bin/GlobalService.svc/searchpeople?firstname=&lastname=&accreditationid=&servicelineid=&industrypracticeid=&country=United+States&pagesize=20002");
		
		// test these 14 first
		//$this->loadUrl("http://www.cbre.us/_vti_bin/GlobalService.svc/searchpeople?firstname=&lastname=&accreditationid=&servicelineid=&industrypracticeid=&country=United+States&city=Akron--C&recindex=0&pagesize=2000");
		//log::error_level(ERROR_DEBUG_VERBOSE);
		//$this->debug=true;
		
		$this->loadUrl("http://www.cbre.us/_vti_bin/GlobalService.svc/locations?country=Canada");
		$this->loadUrl("http://www.cbre.com/people-and-offices",true);
	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();		
		
		if (preg_match("#cbre.com/people-and-offices#",$url))
		{
			$x = new XPath($html);	
			$urls = array();
			foreach ($x->query("//div[contains(@class, 'country-selection-content')]//a") as $node)
			{				
				$country = urlencode(trim($node->textContent));
				$urls[] = "http://www.cbre.us/_vti_bin/GlobalService.svc/locations?country=$country";				
			}
			if (sizeof($urls)>0)			
					$thiz->loadUrlsByArray($urls);
		}
		else 
		if (preg_match("#GlobalService.svc/locations#",$url))
        {			
			$urls = array();
			$json_data = json_decode($html,true);
			foreach ($json_data["GetLocationsResult"] as $loc)
			{				
				if (!empty($loc['Key']))
				{
					$country = urlencode($query["country"]);
					$city = urlencode($loc['Key']);
					$urls[] = "http://www.cbre.us/_vti_bin/GlobalService.svc/searchpeople?firstname=&lastname=&accreditationid=&servicelineid=&industrypracticeid=&country=$country&city=$city&recindex=0&pagesize=20000";
				}				
			}
			if (sizeof($urls)>0)			
					$thiz->loadUrlsByArray($urls);			
        }
		else 
		if (preg_match("#GlobalService.svc/searchpeople#",$url))		
		{
			$json_data = json_decode($html,true);
			
			foreach($json_data['SearchPeopleResult']['Peoples'] as $bigData)
			{
				$address = $bigData['BusinessCard'];
				unset ($bigData['BusinessCard']);

				$data = db::normalize(array_merge( $bigData, $address));
				
				foreach ($data as $k=>$v)
				{
					if (empty($v))// cleanup
						unset($data[$k]);
				}
				
					
				if (empty($data['NICKNAME']))					
					$data['NICKNAME'] = $data['INTERNALID'];
				else
					$thiz->loadUrl("http://www.cbre.us/{$data['NICKNAME']}"); // load secondary
				
				$data['SOURCE_URL'] = $url;
				log::info($data);	
				db::store($type,$data,array('NICKNAME'),true);	
				//log::info("done");
				
					
			}
		}
        else		// do secondary scrape now.
		{
			$x = new XPath($html);	
			
			$sections = array();
			
			foreach ($x->query("//div[@class='text-content']//h3") as $node)
			{				
			
				 $sections[] = $node->textContent;
			}
			
			foreach ($x->query("//*[@class='employee-name']") as $node)
			{				
				 $data =$np->parse($node->textContent);
			}
			
			foreach ($x->query("//*[@class='employee-location']") as $node)
			{		
				$data['TITLE'] = self::cleanup($node->textContent);
			}
			
			foreach ($x->query("//*[@class='employee-speciality']") as $node)
			{							
				 $data['speciality'] = self::cleanup($node->textContent);
			}
			
			foreach ($x->query("//*[@class='employee-serviceline']") as $node)
			{							
				 $data['serviceline'] = self::cleanup($node->textContent);
			}
			
			foreach ($x->query("//*[@class='employee-contactnumbers']") as $node)
			{							
				 $data['employee_contactnumbers'] =$pp->parse($node->textContent);
			}
		$data["FULL_PROFILE"] = "";
			
			foreach($sections as $section)
			{
				//$data[$section] = "";
				// look at each section and try grab data until we see another h3
				$sectionArray = array();
				log::info($section);
				foreach ($x->query("//div[@class='text-content']//h3[contains(text(),'$section')]/following-sibling::*") as $node)
				{
					if ($node->tagName == "h3") // only grab stuff until the next h3
						break;
					$sectionArray[] = self::cleanup($node->textContent);
				}
				if (empty(trim($section)))
					$section="PROFESSIONAL EXPERIENCE";				
				
				/*
				if (preg_match("/(PROFESSIONAL)|(EXPERIENCE)/i",preg_replace("/[^A-Z]/","", strtoupper($section))))
				{
					$section="PROFESSIONAL EXPERIENCE";
				}
				
				if (preg_match("/(ACHIEVEMENTS)|(ACHEIVEMENT)/i",preg_replace("/[^A-Z]/","", strtoupper($section))))
				{
					$section="ACHIEVEMENTS";
				}
				
				*/
					$data["FULL_PROFILE"] .=  $section. "\n".self::cleanup(join("\n ", $sectionArray))."\n\n";
				
				
			}
			
			
			// now figure out nickname
			// url looks like this: http://www.cbre.us/o/akron/people/dean-bacopoulos/Pages/overview.aspx
			$path = parse_url($url,PHP_URL_PATH); 			
			$host = parse_url($url,PHP_URL_HOST); 
			$pathInfo = explode("/",$path);
			
			for($i=0;$i<sizeof($pathInfo)-1;$i++)
			{
				$data['NICKNAME'] = trim(str_replace('-','.',$pathInfo[$i]));					
				
				if ($data['NICKNAME'] == 'people')
				{
					$data['NICKNAME'] = trim(str_replace('-','.',$pathInfo[$i+1]));	
					break;
				}	
				
				if ($data['NICKNAME'] == 'people-and-offices')
				{
					$data['NICKNAME'] = trim(str_replace('-','.',$pathInfo[$i+1]));	
					break;
				}					
			}
			
			
			$data2 = db::normalize($data);
			unset($data);
			$data = array();
			foreach ($data2 as $k=>$v)
			{
				if (empty($v) || empty($k))// cleanup
					continue;
					
				if (isset($data2["PROFESSIONAL_EXPERIENCE"]))
				{
					// fix dupes
					if ($data2["PROFESSIONAL_EXPERIENCE"] == $v && $k != "PROFESSIONAL_EXPERIENCE")
						continue;
				}
				$data[$k] = $v;
			}
				
			log::info($data);
			$data["SOURCE URL 2"] = $url;
			db::store($type,$data,array('NICKNAME'),true);	
			//log::info("done");
		}
	}
	
}



$r= new cbre_us_2017();
$r->parseCommandLine();

