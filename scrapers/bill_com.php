<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class bill_com extends baseScrape
{

   

   public function runLoader()
   {
      $type = get_class();    

      //$this->maxRetries = 100;
      //$this->timeout = 15;
      $this->useCookies=true;
      $this->allowRedirects = true;
      $this->debug=false;
//		$this->proxy="localhost:8888";
	//	$this->noProxy=false;
	    $this->threads=1;
// $this->debug=true;
   //log::error_level(ERROR_DEBUG_VERBOSE);
      
      /*
      db::query("
      UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)      
          ");

     */
      // cananda top 100 cities by population
     // db::query("DELETE FROM raw_data where type = '$type' ");
	//   db::query("DELETE FROM load_queue where type='$type' ");
//      db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");

//      db::query("DROP TABLE $type ");
  //    
/*

      db::query("UPDATE raw_data SET parsed = 0 where parsed = 1 ");

      db::query("DROP TABLE $type ");

     db::query("DELETE FROM raw_data where type = '$type' ");
	   db::query("DELETE FROM load_queue where type='$type' ");
		db::query("DROP TABLE $type ");
		db::query("UPDATE load_queue SET processing = 0 where  type = '$type' ");

		db::query("UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data  WHERE type = '$type') AND type = '$type' ");
*/
	//	db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");
//		db::query("DROP TABLE $type ");

		// do this the easy way
		$urls = array();
		for($i=1;$i<175;$i++)
		{
			$urls[] = "http://www.bill.com/for-accountants/accountant-directory/?loc=&page=$i";
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

   // file_put_contents("$type.html",$html); 
      $webRequests = array();
      $links = array();
      $data = array();


		if (!preg_match("#bill.com/for-accountants/accountant-directory/profile#",$url))
		{
			$urls = array();
			$x = new Xpath($html);
		
			// load the listings
			foreach($x->query("//div[@class='AcctDirectoryText']//a") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
			}	
			
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$x = new Xpath($html);
			$data =array();
			foreach($x->query("//div[@id='innerContainer_accountantProfile']//h3") as $node)
			{
					$data['COMPANY_NAME'] = $node->textContent;
			}
			
			/// find the address....
			foreach($x->query("//td[@class='Location']") as $node)
			{
				$data = array_merge($data,  $ap->parse( preg_replace("#WEBSITE|CONTACT|EMAIL#","",$node->textContent)));
				$data = array_merge($data,  $pp->parse($node->textContent));
			}
			
			$emails = array();
			foreach($x->query("//td[@class='Location']//a[contains(@href,'mailto')]") as $node)
			{
				$data['EMAIL'] = str_replace("mailto:","", $node->getAttribute("href"));
			}

			
			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('RAW_ADDRESS'),true); 
		
		}
   }
}

$r= new bill_com();
$r->parseCommandLine();

