20<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class apta extends baseScrape
{
    public static $_this=null;
 	 public static $fails = 0;

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
$this->proxy = "localhost:9666";

		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->threads=2;
		$this->debug=false;
		$this->maxRetries = 0;
		//
	/*		
		db::query("DROP TABLE $type  ");
		

		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		

	
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	*/	
	//	db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
//db::query("DELETE FROM load_queue where type='$type'");

		//db::query("DELETE FROM load_queue where type='$type'");
		//db::query("DELETE FROM raw_data where type='$type'");	


		// first login and do captcha manually... get the session id and put it here. 
		$this->setCookies(array("ASP.NET_SessionId"=>"u2l42j3hxt12gvcyjitcnolh"));

		$html = $this->get("http://www.apta.org/apta/findapt/index.aspx?navID=10737422525");
		$searchPage = new HtmlParser($html);
		$form = $searchPage->loadViewState();	
		$req = array();

		$reqs = array();
      $result = mysql_query("SELECT CITY, STATE FROM geo.US_CITIES order by pop desc limit 250");      
      while ($r = mysql_fetch_row($result))
      {
			$city = strtoupper($r[0]);
			$state = ucfirst($r[1]);

			$form['ctl00$ContentPlaceHolder1$txtZIP'] = "";
			$form['ctl00$ctl00$ctl00$ContentPlaceHolder$PortalLayout1$ctl12$ctl02$SearchTerms'] = "";	
			$form['ctl00$ctl00$ctl00$ContentPlaceHolder$PortalLayout1$ctl12$ctl02$_ClientState'] = "";		
			$form['ctl00$ctl00$ctl00$PreContentAreaPlaceHolder$PreContentAreaPlaceHolder$WebPartManager1$wp483103960$wp1401076760$tbAdvQuery'] = "";	
			$form['ctl00$ctl00$ctl00$PreContentAreaPlaceHolder$PreContentAreaPlaceHolder$WebPartManager1$wp483103960$wp1401076760$rblSearchType'] = "with all of the words";		
			$form['ctl00$ctl00$ctl00$PreContentAreaPlaceHolder$PreContentAreaPlaceHolder$WebPartManager1$wp483103960$wp1401076760$tbWithout'] = "";	
			$form['ctl00$ctl00$ctl00$PreContentAreaPlaceHolder$PreContentAreaPlaceHolder$WebPartManager1$wp483103960$wp1401076760$ddlSort'] = "Relevance";		
			$form['ctl00$ContentPlaceHolder1$txtCity'] = $city;		
			$form['ctl00$ContentPlaceHolder1$lstStateProvince'] = $state;		 
			$form['ctl00$ContentPlaceHolder1$radDist'] = "100";		
			$form['ctl00$ContentPlaceHolder1$txtProvider'] = "";		
			$form['ctl00$ContentPlaceHolder1$radPracArea'] = " ";		 
			$form['ctl00$ContentPlaceHolder1$btnSearchcaptcha'] = "Search";		
			
			
			$fail = self::$fails;

			$req = new WebRequest("http://www.apta.org/apta/findapt/index.aspx?navID=10737422525&x=".urlencode("$city$state"),$type,"POST",$form);
			print_R($req);
			$this->loadWebRequest($req);
			$this->queuedFetch();
			$this->parseData();
		}
		
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath(preg_replace("/(<.[^>]+>)/"," \\1 ", nl2br($html)));	
		
/*
		if (strpos($html, "No records found."))
			self::$fails++;

		if (self::$fails > 5)
		{
			self::$fails = 0;
			exit;
		}*/

		// load the listings.

		$links = array();
		foreach($x->query("//a[contains(@href,'apta/findapt/index.aspx?seqn=')]") as $node)
		{
			$link  = self::relative2absolute($url,$node->getAttribute("href"));
			$links[] = $link;
			// make sure the referer is set to this url
			$thiz->setReferer($link,$url);	
		}
		$thiz->loadUrlsByArray($links);	
		
		if (strpos($url, "index.aspx?seqn=") > 0)
		{			
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();


			$data = array();

			foreach ($x->query("//div[@class='detailname']") as $node)
			{
				$data['NAME'] = $node->textContent;
			}
			
			foreach($x->query("//*[@id='ctl00_ContentPlaceHolder1_pnlDetail']/table[2]/tr[3]/td/table/tr[3]//span[@class='h4']") as $node)
			{
				$data['PRACTICE_NAME'] = $node->textContent;
			}
			
			foreach($x->query("//*[@id='ctl00_ContentPlaceHolder1_pnlDetail']/table[2]/tr[3]/td/table/tr[3]//span[@class='biggertext']") as $node)
			{
				$data = array_merge($data, $ap->parse($node->c14n()));
			}
			
			
			foreach($x->query("//*[@id='ctl00_ContentPlaceHolder1_pnlDetail']/table[2]/tr[3]/td/table/tr[4]//span[@class='biggertext']") as $node)
			{
				$newdata  = $kvp->parse($node->c14n());
				foreach($newdata as $k=>$v)
				{
					if (! (
						strtoupper(trim($k)) ==	'PHONE' ||
						strtoupper(trim($k)) ==	'FAX' ||
						strtoupper(trim($k)) ==	'WEB_SITE' ||
						strtoupper(trim($k)) ==	'E_MAIL' ||
						strtoupper(trim($k)) ==	'ABPTS_CERTIFICATION_S' ||
						strtoupper(trim($k)) ==	'SOURCE_URL' ||
						strtoupper(trim($k)) ==	'OTHER_PHONE' ||
						strtoupper(trim($k)) ==	'COUNTRY'))
					{unset($newdata[$k]);
					}

					if (preg_match("/[0-9]/",$k) )
					{
						log::info("$k to long");
						unset($newdata[$k]);
					}
				}
				$data = array_merge($data, $newdata);
			}

			foreach($x->query("//table[@class='searchresults']//td") as $node)
			{
				$newdata = $kvp->parse($node->textContent);
				
				foreach($newdata as $k=>$v)
				{
					if (	strtoupper($k) == 'ABPTS_CERTIFICATION_S' || 
							strtoupper($k) == 'OFFICE_HOURS' || 
							strtoupper($k) == 'APTA__SECTION__MEMBER' || 
							strtoupper($k) == 'PRACTICE__FOCUS' )
					{
						$data[$k] = trim($v);
					}
				}
			}
			
			

			if (!empty($data))
			{
				unset($data['RAW_ADDRESS']);
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('NAME','PHONE'));	
			}		
			
		}
	}
}

$r= new apta();
$r->parseCommandLine();

