<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class opto_ca extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
	//	$this->noProxy=false;
//		$this->proxy = "localhost:9666";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		
		//
	/*			
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");		
		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='opto_ca' and parsed = 1 ");
	*/
	//	
	

//	db::query("UPDATE raw_data set parsed = 0 where type='opto_ca' and parsed = 1 ");
//		db::query("	DROP TABLE opto_ca"); 

		// load all cities 

      $result = mysql_query("SELECT distinct state from geo.canadian_cities");
      $webRequests = array();
      while ($r = mysql_fetch_row($result))
      {
			$state = $r[0];
			$url = "http://opto.ca/find-an-opto/?x=$state";
			$data = "submit_action=find-opto-content-form&findopto_self_province=$state&findopto_self_lastname=Last+Name&findopto_self_city=&findopto_self_postalcode=Postal+Code";

			$webRequests[] = new WebRequest($url,$type,'POST',$data);
        
      }

		log::info("Loaded ".sizeof($webRequests). " states");
      return  $this->loadWebRequests($webRequests);
   
	}
	

	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		$html = '<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$html;

		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		$ep = new Email_Parser();

		parse_str(parse_url($url,PHP_URL_QUERY),$qs);

		if (!empty($qs['x']))
		{
			$x = new XPath($html);	

			$state = $qs['x'];
			foreach ($x->query("//select[@id='findopto_self_city']//option") as $node)
			{
				$city = urlencode($node->getAttribute("value"));
				$href= "http://opto.ca/find-an-opto/?l=$city,$state";

				$data = "submit_action=find-opto-content-form&findopto_self_province=$state&findopto_self_lastname=Last+Name&findopto_self_city=$city&findopto_self_postalcode=Postal+Code";
				$webRequests[] = new WebRequest($href,$type,'POST',$data);
			}
			log::info("Loaded ".sizeof($webRequests). " cities");
			return  $thiz->loadWebRequests($webRequests);
		}
		
		else if (!empty($qs['l']))
		{
			log::info("In Listing");
			$xListing = new XPath($html);	
			foreach ($xListing->query("//div[contains(@class,'result-opto-wrapper')]") as $nodeListing)
			{
				$nodeHtml = mb_convert_encoding($nodeListing->c14n(), "UTF-8", "UTF-8") ;

				log::info(mb_detect_encoding ($nodeHtml));
				$x = new XPath('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$nodeHtml);	

				$data = array();
				foreach ($x->query("//div[contains(@id,'addr_')]") as $node)
				{
					list($data['NAME'],$address) = json_decode($node->textContent);
					$data = array_merge($data,$ap->parse($address));
				}

				foreach ($x->query("//p") as $node)
				{
					$data = array_merge($data,$pp->parse($node->textContent));
				}
				foreach ($x->query("//a") as $node)
				{
					$data['WEBSITE'] = $node->getAttribute("href");
					break;
				}
			
				$docs = array();
				foreach ($x->query("//h4[contains(@class,'result-opto-names')]") as $node)
				{
					if (!empty($node->textContent))
						$docs[] = trim($node->textContent ," \t\n\r\0\x0B,");
				}
				$data['DOCTORS'] = join(", ", $docs);
					

				if (!empty($data['NAME']))
				{
					$data['SOURCE_URL'] = $url;
					log::info($data);		
					db::store($type,$data,array('NAME','PHONE'));	
				}
			
			}
		}
		else
		{
			log::info("Invalid Listing");
		}
	

	}
}

$r= new opto_ca();
$r->parseCommandLine();

