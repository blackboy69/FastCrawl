<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class rocketlawyer extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		//$this->noProxy=false;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
	/*	
		
		
			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			

		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='rocketlawyer' and parsed = 1 ");

		db::query("	DROP TABLE rocketlawyer"); 
		db::query("UPDATE raw_data set parsed = 0 where type='rocketlawyer' and parsed = 1 ");
	*/

		$this->loadUrlsByState( "http://www.rocketlawyer.com/profiles/legal-search.aspx?practice=&city=&zip=&state=&firstName=&lastName=&keywords=&firm=");

		//db::query("UPDATE load_queue set processing = 0 where type='rocketlawyer' and processing = 1 ");
		//db::query("UPDATE raw_data set parsed = 0 where type='rocketlawyer' and parsed = 1 ");
		//db::query("DROP TABLE $type");	
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$hp=new Operating_Hours_Parser();
		$kvp = new KeyValue_Parser();


		// parse listings
		if (preg_match("/legal-search\.aspx/",$url))
		{
			$urls = array();
			// load listings
			foreach ($x->query("//a[@class='lawyerName']") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}

			// load next pages
			foreach ($x->query("//table[@class='numericPages']//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);
		}
		// parse details
		else
		{
			$data = array();
			foreach ($x->query("//h1[@class='LawyerName']") as $node)
			{
				$data['LAWYER_NAME'] = self::cleanup($node->textContent);
			}

			foreach ($x->query("//div[@class='Practice']") as $node)
			{
				$data['PRACTICE_NAME'] =self::cleanup($node->textContent);
			}

			foreach ($x->query("//div[@class='Practice']/following-sibling::div") as $node)
			{
				$data= array_merge($data,$ap->parse($node->c14n()));
			}

			foreach ($x->query("//img[@id='ctl00_ctl00_ctl00_SiteMasterBody_ContentPlaceHolder1_Body_ProfileHeader_OnCallBadge']") as $node)
			{
				$data['ON_CALL'] ="YES";
			}		

			foreach ($x->query("//div[contains(@class,'ClaimYourProfile')]") as $node)
			{
				$data['UNCLAIMED_PROFILE'] ="YES";
			}		
			
			
			$areasOfPractice = array();
			foreach ($x->query("//h3[@class='areasOfPractice']") as $node)
			{
				$areasOfPractice[] = trim($node->textContent);
			}
			$data['AREAS_OF_PRACTICE'] = join(", ",$areasOfPractice);

			$phoneUrl = str_replace("/view-profile-","/contact-",$url);
			$phoneX = new Xpath($thiz->get($phoneUrl));
			foreach ($phoneX->query("//*[@id='ctl00_ctl00_SiteMasterBody_ContentPlaceHolder1_EntryPanel']/table/tr[2]") as $phoneNode)
			{
				$data = array_merge($data, $kvp->parse(self::cleanup($phoneNode->textContent)));
			}

			if (!empty($data['LAWYER_NAME']))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				$id = db::store($type,$data,array('LAWYER_NAME','PHONE','ZIP'));	

			
			}
		
		}

	

	}
}

$r= new rocketlawyer();
$r->parseCommandLine();

