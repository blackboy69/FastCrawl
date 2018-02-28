<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class doctorsofoptometry extends baseScrape
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
		db::query("UPDATE raw_data set processing = 0 where type='doctorsofoptometry' and parsed = 1 ");
	*/
	//	
	

//		db::query("UPDATE raw_data set parsed = 0 where type='doctorsofoptometry' and parsed = 1 ");
//		db::query("	DROP TABLE doctorsofoptometry"); 

		// load all cities 

      $result = mysql_query("SELECT distinct city,state from geo.canadian_cities order by pop desc");
      $webRequests = array();
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode($r[0]);
			$state = $r[1];
			$urls[] = "http://$state.doctorsofoptometry.ca/find-a-doctor-results?province=$state&city=$city&postal=&lastname=&fad_submit=SUBMIT";
      }
      return  $this->loadUrlsByArray($urls);   
	}
	

	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		$ep = new Email_Parser();

		parse_str(parse_url($url,PHP_URL_QUERY),$qs);
		
		log::info("In Listing");
		$xListing = new XPath($html);	
		foreach ($xListing->query("//li[@class='member']") as $nodeListing)
		{
			$nodeHtml = $nodeListing->c14n();
			$x = new XPath('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$nodeHtml);	
			$data = array();

			foreach ($x->query("//span[@class='name']") as $node)
			{
				$data['NAME'] = $node->textContent;
			}					
			if (! empty($data['NAME'] ))
			{
				$address = array();
				foreach ($x->query("//span[@class='address']") as $node)
				{
					if (! empty($node->textContent))
						$address[] = $node->textContent;	
				}
				$data = array_merge($data,$ap->parse(join(",", $address)));

				foreach ($x->query("//span[@class='phone']") as $node)
				{
					$data['PHONE'] = str_ireplace("PHONE: ", "",$node->textContent);
				}					

				foreach ($x->query("//span[@class='fax']") as $node)
				{
					$data['FAX'] = str_ireplace("FAX: ", "",$node->textContent);
				}					

				if (!empty($data['PHONE']))
				{
					$data['SOURCE_URL'] = $url;
					log::info($data);		
					db::store($type,$data,array('NAME','PHONE'));	
				}
			}
		
		}
	

	}
}

$r= new doctorsofoptometry();
$r->parseCommandLine();

