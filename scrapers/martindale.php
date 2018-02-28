<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class martindale extends baseScrape
{
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
		//$this->switchProxy(); // don't expose our real ip to these peeps.



		//$this->proxy = "localhost:8888";

		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		$this->maxRetries = 10;
		//
		
		// to get referrers
//		db::query("UPDATE  load_queue set processing=0 where type='$type' and processing=0");
	//	db::query("UPDATE raw_data set parsed = 0 where type='martindale' and parsed = 1  ");

	/*		
		

	
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	*/				

		$this->LoadUrl("http://www.martindale.com/united-states-lawyers.htm");	

		
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();


		// load the listings.
		
		if (strpos($url, "-lawyers.htm") >0)
		{
			$x = new XPath($html);	
			$links = array();
			foreach($x->query("//table[@style='width:600px' or @width='600px']//a") as $node)
			{
				$link  = self::normalizeHref("http:\/\/www.martindale.com",$node->getAttribute("href"));
				$links[] = $link;
				// make sure the referer is set to this url
				$thiz->setReferer($link,$url);	
			}			
			foreach ($x->query("//table[@class='results']//a") as $node)
			{
				$link  = self::normalizeHref("http:\/\/www.martindale.com",$node->getAttribute("href"));
				$links[] = $link;
				// make sure the referer is set to this url
				$thiz->setReferer($link,$url);	
			}				
			

			if (!empty($links))
				$thiz->loadUrlsByArray($links);	
		}

		if (strpos($url, "-lawyer.htm") >0)
		{
			$x = new XPath($html);	

			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();


			$data = array();

			foreach ($x->query("//td[@class='profile-header-line']//h1") as $node)
			{
				$data['NAME'] = $node->textContent;
			}
			
			foreach ($x->query("//td[@class='profile-header-line']//tr[2]/td/div[1]") as $node)
			{
				$data['TITLE'] = $node->textContent;
			}
			
			foreach ($x->query("//td[@class='profile-header-line']//tr[2]/td/div[2]") as $node)
			{
				$data['PRACTICE_NAME'] = $node->textContent;
			}

			foreach ($x->query("//td[@class='profile-header-line']//tr[2]/td/div[3]") as $node)
			{
				$data = array_merge($data, $ap->parse($node->textContent));
			}

			foreach ($x->query("//div[@class='profile-header-label']") as $node)
			{
				$data[self::cleanup($node->textContent)] = $node->nextSibling->textContent;
			}

			foreach ($x->query("//img[contains(@src,'ratings_bar']") as $node)
			{
				$data["PEER_RATING"] = $node->textContent;
			}
			
			$practiceAreas = array();
			foreach ($x->query("//div[@id='divAopText']//li") as $node)
			{
				$practiceAreas[] = $node->textContent;
			}
			$data['PRACTICE_AREAS'] = join(",", $practiceAreas);			

			if (!empty($data))
			{
				unset($data['RAW_ADDRESS']);
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('SOURCE_URL'));	
			}		
		}
	}
}

$r= new martindale();
$r->parseCommandLine();

