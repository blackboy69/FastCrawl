<?
include_once "config.inc";
//R::freeze();

class avisonyoung_com extends baseScrape
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
		//$this->loadUrl("http://www.cbre.us/_vti_bin/GlobalService.svc/searchpeople?firstname=&lastname=&accreditationid=&servicelineid=&industrypracticeid=&country=United+States&pagesize=20002");
		
		// test these 14 first
		//$this->loadUrl("http://www.cbre.us/_vti_bin/GlobalService.svc/searchpeople?firstname=&lastname=&accreditationid=&servicelineid=&industrypracticeid=&country=United+States&city=Akron--C&recindex=0&pagesize=2000");
		//log::error_level(ERROR_DEBUG_VERBOSE);
		//$this->debug=true;
		
		//$this->loadUrl("http://www.cbre.us/_vti_bin/GlobalService.svc/locations?country=Canada");
		for($i=0;$i<200;$i++)
			$urls[] = "http://www.avisonyoung.com/find-professionals?page=$i&name=&specialty=all&location=all&op=Search&form_build_id=form-3d1c1260afda6f4add9176f97e4d8506&form_id=ay_professionals_search_page_form";
		
		$this->loadUrlsByArray($urls);
	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$np=new Name_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();		
		$x = new XPath($html);	
		$data = array();
		if (preg_match("#http://www.avisonyoung.com/find-professionals?#",$url))
		{
			
			$urls = array();
			foreach ($x->query("//td[contains(@class, 'views-field-field-lastname-value')]//a") as $node)
			{				
				$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			if (sizeof($urls)>0)			
					$thiz->loadUrlsByArray($urls);
		}
		else
		{		
		
			// get name
			foreach ($x->query("//h1[@class= 'title']") as $node)
			{	
				$data = array_merge($data, $np->parse($node->textContent));
				break;
			}
			
			// get title
			foreach ($x->query("//div[@class= 'node-professional-titles']") as $node)
			{	
				$data['TITLE'] = trim($node->textContent);
			}
			
			// get specialities
			foreach ($x->query("//div[@class= 'node-professional-specialties']") as $node)
			{	
				$data['SPECIALTIES'] = trim($node->textContent);
			}
			
			// address
			foreach ($x->query("//div[contains(@class, 'field-office-address')]") as $node)
			{	
				$data = array_merge($data, $ap->parse($node->textContent));
			}
			
			// get the phone numbers
			foreach ($x->query("//td[contains(@class, 'node-professional-phone')]//div[contains(@class,'field')]") as $node)
			{	
				$data = array_merge($data, $kvp->parse($node->textContent));
			}
			
			
			//email	address
			foreach ($x->query("//div[contains(@class, 'node-professional-link-email')]") as $node)
			{	
				$data = array_merge($data, $ep->parse($node->textContent));
			}
			
			// PROFILE
			foreach ($x->query("//div[contains(@class, 'field-field-profile-body')]") as $node)
			{	
				$data['PROFESSIONAL_PROFILE'] = trim($node->textContent);
			}
			
			// credentials
			foreach ($x->query("//div[contains(@class, 'field-field-credentials-body')]") as $node)
			{	
				$data['CREDENTIALS'] = trim($node->textContent);
			}
									
			$data = db::normalize($data);			
				
			$data["SOURCE URL"] = $url;
			log::info($data);			
			
			db::store($type,$data,array('FIRST_NAME','LAST_NAME','RAW_ADDRESS'),true);	

		}
	}
}

$r= new avisonyoung_com();
$r->parseCommandLine();

