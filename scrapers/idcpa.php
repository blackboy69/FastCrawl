<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class idcpa extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		//$this->maxRetries = 100;
		//$this->timeout = 15;
		$this->threads=10;
		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->debug=false;
//		$this->threads=2;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)		 
			 ");

		*/
		// cananda top 100 cities by population
		//db::query("DELETE FROM raw_data where type = '$type' ");
//		db::query("DELETE FROM load_queue where type='$type' and processing = 0");
		db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");

		db::query("DROP TABLE $type ");
		//db::query("UPDATE load_queue SET processing = 1 where type = '$type'  ");

		$urls = array();
		for ($i = 0; $i< 10000; $i++)
		{
			$urls[] = "http://www.idcpa.org/Locator/FirmDetails.aspx?firm=".sprintf("%05d",$i);


		}
		$this->loadUrlsByArray($urls);
	}
/*
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

		if (strlen($html)<5000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
	}*/


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();

		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$kvp = new KeyValue_Parser();	
		$urls = array();
		log::info($url);
		//parse_str(parse_url($url,PHP_URL_QUERY),$query); 

	//	file_put_contents("$type.html",$html);	
		$webRequests = array();
		$links = array();
		$data = array();
	/*	if (preg_match("#find-a-cpa/listing#",$url))
		{

			$x =  new  XPath($html);	
			foreach($x->query("//a[contains(@href, 'find-a-cpa/profile')]") as $node)
			{
				  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			if (!empty($links))
				$thiz->loadUrlsByArray($links);

			$data = $x->loadViewState();
			$data['ctl00$smOne'] = 'ctl00$cpMainContent$pnlSearch|ctl00$cpMainContent$gvListOfResults';
			$data['ctl00$cpMainContent$txtCPAName'] = '';
			$data['ctl00$cpMainContent$txtFirmName'] = '';
			$data['ctl00$cpMainContent$ddlState'] = '- any -';
			$data['ctl00$cpMainContent$ddlCity'] = '- any -';
			$data['ctl00$cpMainContent$ddlArea'] = '';
			$data['ctl00$cpMainContent$ddlExpertise'] = '';
			$data['__LASTFOCUS'] = '';
			$data['__ASYNCPOST'] = 'true';

			// load next page links
			foreach($x->query("//a[contains(@href,'Page$')]") as $node)
			{
				$pageNumber = $node->textContent;				
				$clickEvent = $x->getClickEvent($node->getAttribute("href"));
				list($junk, $pageNumber) = explode('$',$clickEvent["__EVENTARGUMENT"]);

				// grab the urls from the listing
				$data = array_merge($data, $clickEvent);
				$webRequests[] = new WebRequest("https://www.idcpa.org/resource-center/resources-for-the-public/find-a-cpa/listing?f1=$pageNumber",$type,"POST", $data);
			}

			if (sizeof($webRequests)>0)
			{
				$thiz->loadWebRequests($webRequests);
				return;
			}
		}
		else*/
		{
			$x =  new  XPath($html);	
			$data = array();
			
			foreach($x->query("//span[contains(@id,'FirmName')]") as $node)
			{
				$data['COMPANY'] =  self::cleanup($node->textContent);
			}

			if ($data['COMPANY'] == "Details Not Found For This Firm")
				return;

			foreach($x->query("//span[contains(@id,'PostalAddresses')]") as $node)
			{
				$data = array_merge($data,$ap->parse($node->textContent));
			}

			foreach($x->query("//span[contains(@id,'ElectronicAddresses')]") as $node)
			{
				$data = array_merge($data,$pp->parse($node->textContent));
				$data = array_merge($data,$ep->parse($node->textContent));
			}

			foreach($x->query("//span[contains(@id,'FirmCpas')]") as $node)
			{
				$data['CONTACT_S'] = self::cleanup(strip_tags(preg_replace("#<br>#",";", $node->c14n())));
			}
			$data['COUNTRY'] = 'United States';
			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('SOURCE_URL'));			
		}
	}
}


$r= new idcpa();
$r->parseCommandLine();

