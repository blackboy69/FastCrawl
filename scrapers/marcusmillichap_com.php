<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class marcusmillichap_com extends baseScrape
{

   

   public function runLoader()
   {
      $type = get_class();    

      //$this->maxRetries = 100;
      //$this->timeout = 15;
      $this->useCookies=true;
      $this->allowRedirects = true;
      $this->debug=false;
//	    $this->threads=1;
// $this->debug=true;
   //log::error_level(ERROR_DEBUG_VERBOSE);
      
      /*
      db::query("
      UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)      
          ");

     
      // cananda top 100 cities by population
      db::query("DELETE FROM raw_data where type = '$type' ");
	   db::query("DELETE FROM load_queue where type='$type' ");
//      db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");

      db::query("DROP TABLE $type ");
  //    
 */ 

  //   db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' and url not like '%ervpro.com/search/locator%'");

    //  db::query("DROP TABLE $type ");
/*
     db::query("DELETE FROM raw_data where type = '$type' ");
	   db::query("DELETE FROM load_queue where type='$type' ");
		db::query("DROP TABLE $type ");
		db::query("UPDATE load_queue SET processing = 0 where  type = '$type' ");

		db::query("UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data  WHERE type = '$type') AND type = '$type' ");

		db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");
		db::query("DROP TABLE $type ");*/
		
//		db::query("DELETE FROM raw_data where type = '$type' ");
//	   db::query("DELETE FROM load_queue where type='$type' ");

	//	db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");
//		db::query("DROP TABLE $type ");
		
		$this->loadUrlsByCity("http://www.marcusmillichap.com/about-us/agents/search?1=1&keyword=%CITY%&a=true&o=true&pg=1");
		//$this->loadUrl("http://inbound.org/members/all/hot?&per_page=48",$force=true);

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
		$np = new Name_Parser();
      $urls = array();
      log::info($url);
      //parse_str(parse_url($url,PHP_URL_QUERY),$query); 

   // file_put_contents("$type.html",$html); 
      $webRequests = array();
      $links = array();
      $data = array();


		if (preg_match("#about-us/agents/search?#",$url))
		{
			$urls = array();
			$x = new Xpath($html);
			foreach($x->query("//a[contains(@href,'about-us/agents/')]") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
				//$host = parse_url($node->getAttribute("href"),PHP_URL_HOST);
			}	
			foreach($x->query("//ul[@class='pagination']//a") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
				//$host = parse_url($node->getAttribute("href"),PHP_URL_HOST);
			}	
			
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$x = new Xpath($html);
			$data =array();

			// FULL NAME
			foreach($x->query("//div[@class='agentDetails']//div[@class='desc']//h2") as $node)
			{				
				$data = array_merge($data, $np->parse($node->textContent));
			}
	
			foreach($x->query("//div[@class='agentDetails']//div[@class='desc']//div[@class='innerOne']//strong") as $node)
			{				
				$data["TITLE"] =  $node->textContent;
			}
	
			// phone
			foreach($x->query("//div[@class='agentDetails']//div[@class='desc']//div[@class='innerOne']") as $node)
			{				
				$data = array_merge($data, $kvp->parse($node->textContent));
			}

			$specialities = array();
			// Specialties
			foreach($x->query("//div[@class='innerTwo']//ul[@class='linksList']//li") as $node)
			{				
					 $specialities[] = $node->textContent;
			}
			$data["SPECIALTIES"] = join(", ",$specialities);

			
			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('SOURCE_URL'),true); 
		
		}
   }
}

$r= new marcusmillichap_com();
$r->parseCommandLine();

