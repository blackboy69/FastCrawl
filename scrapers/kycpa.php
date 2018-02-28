<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class kycpa extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		//$this->maxRetries = 100;
		//$this->timeout = 15;
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
//		db::query("DELETE FROM raw_data where type = '$type' ");
//		db::query("DELETE FROM load_queue where type='$type'");
		db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");
		db::query("DROP TABLE $type ");
		//db::query("UPDATE load_queue SET processing = 1 where type = '$type' ");

		$webRequests= array();
//		$this->noProxy= false;
//		$this->proxy = "localhost:8888";

		$html = $this->get("http://www.kycpa.org/Public/Referral/findcpa.aspx");
		$page = new HtmlParser($html);		
		$data = $page->loadViewState();

		$data['ctl00$PanelContent$tbCompany']=  '';
		$data['ctl00$PanelContent$tbZip']=  '';
		$data['ctl00$PanelContent$boxLocation']=  'All';
		$data['ctl00$PanelContent$boxIndustry']=  'All';
		$data['ctl00$PanelContent$boxServices']=  'All';
		$data['ctl00$PanelContent$btnSearch2.x']=  '35';
		$data['ctl00$PanelContent$btnSearch2.y']=  '10';	

		log::info("Perform Search");
		$this->LoadPostUrl("http://www.kycpa.org/Public/Referral/findcpa.aspx",$data);
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

		file_put_contents("$type.html",$html);	

		$links = array();
		$data = array();
		if (preg_match("#FindCPAList.aspx#",$url))
		{

			$x =  new  XPath($html);	
			foreach($x->query("//a[contains(@href, 'FindCPADetails')]") as $node)
			{
				  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			if (!empty($links))
				$thiz->loadUrlsByArray($links);
		}
		else
		{
			$x =  new  XPath($html);	
			$keys = array();
			$values = array();
			$data = array();
			
			foreach($x->query("//*[contains(@id,'FirmName')]") as $node)
			{
				$data['COMPANY'] =  self::cleanup($node->textContent);
			}

			foreach($x->query("//tr[contains(@id,'firmProfile')]//td[@class = 'profileLabel']") as $node)
			{
				$keys[] = self::cleanup($node->textContent);
			}

			foreach($x->query("//tr[contains(@id,'firmProfile')]//td[@class = 'profileDetail']") as $node)
			{
				$values[] =  self::cleanup($node->textContent);
			}

			$key = "ADDRESS";

			for($i=0;$i< sizeof($values) ; $i++ )
			{
				if (!empty($keys[$i]))
					$key = $keys[$i];


				if (empty($data[$key]))
					$data[$key] = $values[$i];
				else
				{
					// if it is an email treat special
					if (preg_match("#.+\@.+\..+#", $values[$i]))
						@$data["EMAIL"] .= $values[$i]." ";
					else
						$data[$key] = "{$data[$key]}, {$values[$i]}";
				}


			}
			
			$data['COUNTRY'] = 'United States';

			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('SOURCE_URL'));			
		}
	}
}


$r= new kycpa();
$r->parseCommandLine();

