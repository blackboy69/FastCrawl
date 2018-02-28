<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class angieslist extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

//		$this->maxRetries = 100;
	//	$this->timeout = 15;
		$this->useCookies=true;
		$this->allowRedirects = true;
	//	$this->debug=false;
//		$this->threads=4;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		$this->proxy='localhost:8888';
		$this->noProxy=false;
		/*
	   db::query("
		UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)		 
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='angieslist' and parsed = 1   ");
		*/
		// cananda top 100 cities by population
		//		db::query("UPDATE raw_data set parsed = 0 ");

		//$this->noProxy=false;
		//$this->useHmaProxy=false;

		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";

		//$this->loadUrlsByCity("http://www.angieslist.com/search?tracks=true&search_terms=plumbers+%26+plumbing+contractors&geo_location_terms=%CITY%,%STATE%")	;
		//$this->loadUrlsByCity("http://www.angieslist.com/search?tracks=true&search_terms=Heating+And+Air+Conditioning&geo_location_terms=%CITY%,%STATE%")	;
		$this->Login();

		$html = $this->get("http://my.angieslist.com/Angieslist/MyAngie/");
		$x = new HtmlParser($html);
		$form = $x->GetForm();
		$toPost = array();
		foreach($form[0] as $k =>$v)
		{
			if (preg_match("/MemberPoll/",$k) )
				continue;
			$toPost[$k] = $v;
		}

		$toPost['ctl00$TopMenuTabId$UniboxSearch$SearchTerm$tb'] = "Plumbing";


		$url = "http://my.angieslist.com/Angieslist/MyAngie/CheckTheList/CategoryRequestResults.aspx?st=&catid=107&coup=1&zip=&city=&maid=2865771&ccmt=0&gr=-1&mz=0&lqsrc=1";

		$result = mysql_query("SELECT CITY,STATE FROM geo.US_CITIES LIMIT 1000");      
      while ($r = mysql_fetch_row($result))
      {
			$city=$r[0];
			$state = $r[1];

			$toPost['ctl00$TopMenuTabId$UniboxSearch$searchNearTextBox'] = "$city, $state";		
			$html = $this->post($url,$toPost);
			
			if (preg_match("/Sign in using your Angie's List account/", $html))
			{
				$this->Login();
				$html = $this->post($url,$toPost);
			}

			$wr = new WebRequest($url,$type,"POST",$toPost);        
			$this->loadWebRequest($wr);
			$this->queuedFetch();
      }


	}

	static function Login()
	{
		$thiz = self::getInstance();
		$loginHtml =  $thiz->get("https://my.angieslist.com/AngiesList/login.aspx");

		$x = new HtmlParser($loginHtml);
		$post = $x->GetForm();
		$toPost = $post[0]; //first form...
		
		$toPost['ctl00$ContentPlaceHolderMainContent$LoginControl$UserNameTextbox'] = 'byronwhitlock@gmail.com';
		$toPost['ctl00$ContentPlaceHolderMainContent$LoginControl$UserPasswordTextbox'] = 'bdmac69';
		$toPost['ctl00$ContentPlaceHolderMainContent$LoginControl$userClientTime'] = date("D, j M Y H:i:s ", time()+ 420*60) . "GMT";// 'Fri, 24 May 2013 17:30:52 GMT';
		$toPost['ctl00$ContentPlaceHolderMainContent$LoginControl$userClientTimeZoneOffset'] = '420';
		unset($toPost['action']);

		$html = $thiz->post("https://my.angieslist.com/AngiesList/login.aspx",$toPost);

		if (!preg_match("/Your login was successful!/",$html))
		{
			log::info("Could not login");
			exit;		
		}
	}

	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		if (strpos($html,"The Three Laws of Robotics are as follows:"))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("ERROR! ERROR! FORBIDDEN ACCESS!");
					
			$html=null;
		}

		if (strlen($html)<15000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
	}



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$xTop = new  XPath($html);	

		// get next page links
		foreach($xTop->query("//div[@class='page-navigation']//a") as $nodeTop)
		{
			$urls[] = self::relative2absolute($url,$nodeTop->getAttribute("href"));
		}

		if (!empty($urls))
			$thiz->loadUrlsByArray($urls);



		foreach($xTop->query("//div[@class='listing-content']") as $nodeTop)
		{
			$thiz->parseListings($url, $nodeTop);
			echo ".";
		}
		
		//overwrite featured listings
		foreach($xTop->query("//div[@class='featured']//div[@class='sb-group']/ul") as $nodeTop)
		{
			$thiz->parseListings($url, $nodeTop);
			echo "+";
		}
	}

	function parseListings($url, $nodeTop)
	{
			$x = new XPath($nodeTop);
			$type = get_class();		
			$thiz = self::getInstance();

			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();

			$data = array();

			foreach ($x->query("//h3[contains(@class,'business-name') or contains(@class,'business-title')]") as $node)
			{
				$data['COMPANY'] = trim($node->textContent);
			}
			foreach ($x->query("//span[contains(@class,'phone')]") as $node)
			{
				$p = trim($node->textContent);
				if (!empty($p))
				{
									$data['PHONE']=$p;
									break;
				}
			
			}

		
			foreach ($x->query("//a[contains(@class, 'website')]") as $node)
			{
				$href = trim($node->getAttribute("href"));
				$data["WEBSITE"] =$href;
			}	
			
			foreach ($x->query("//span[contains(@class,'listing-address')]") as $node)
			{
				$data = array_merge($data, $ap->parse($node->c14n()) );
			}
			unset ($data['RAW_ADDRESS'] );

			foreach ($x->query("//div[@class='rating']//p") as $node)
			{

				$data['RATING'] = trim($node->textContent);
			}

			foreach ($x->query("//p[@class='review-count']") as $node)
			{
				$data['NUM_REVIEWS'] = trim($node->textContent);
			}
			// pull category
			$categories=array();
			foreach ($x->query("//ul[@class='business-categories']//li") as $node)
			{
				$cat = trim($node->textContent);
				if (!empty($cat))
					$categories[] =  $cat ;
			}
			$data['CATEGORIES'] = join(",", $categories);

			$data['ACCOUNT_TYPE'] = $nodeTop->getAttribute("class");


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
				//log::info($data);
				db::store($type,$data,array('COMPANY','PHONE','ADDRESS','ZIP', 'ACCOUNT_TYPE'));	
			}		
	}
}

$r= new angieslist();
$r->parseCommandLine();

