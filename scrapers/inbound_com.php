<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class inboud_com extends baseScrape
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
/*
      db::query("DELETE FROM raw_data where type = '$type' ");
	   db::query("DELETE FROM load_queue where type='$type' ");
		
		db::query("UPDATE load_queue SET processing = 0 where  type = '$type' ");
		db::query("UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data  WHERE type = '$type') AND type = '$type' ");

		db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");
		db::query("DROP TABLE $type ");*/
		$this->loadUrl("http://inbound.org/members",$force=true);
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
      $urls = array();
      log::info($url);
      //parse_str(parse_url($url,PHP_URL_QUERY),$query); 

   // file_put_contents("$type.html",$html); 
      $webRequests = array();
      $links = array();
      $data = array();


		if (preg_match("#inbound.org/members#",$url))
		{
			$urls = array();
			$x = new Xpath($html);
			foreach($x->query("//ul[@class='pagination']//a") as $node)
			{
				IF (self::relative2Absolute($url,$node->getAttribute("href")) != "")
					$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));

			}

			foreach($x->query("//a[@class='avatar']") as $node)
			{
				IF (self::relative2Absolute($url,$node->getAttribute("href")) != "")
					$urls[]= self::relative2Absolute($url,$node->getAttribute("href"));
			}
	
			
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$x = new Xpath($html);


			foreach($x->query("//h1") as $node)
			{

				$data["NAME"] = iconv('UTF-8', 'ASCII//TRANSLIT', self::cleanup(strip_tags(preg_replace("#<span.+#","", $node->c14n()))));

			}

			foreach($x->query("//div[@class='member-banner-tagline']") as $node)
			{
				if (preg_match("#(.+) at (.+) in (.+)#", $node->textContent,$matches))
				{
					$data['TITLE'] = self::cleanup($matches[1]);
					$data['COMPANY'] = self::cleanup($matches[2]);
					$data['LOCATION'] = self::cleanup($matches[3]);
					$data = array_merge($data, $ap->parse($data['LOCATION']));
					unset($data["RAW_ADDRESS"]);
				

					if (strpos($data['LOCATION'],"United States"))
						$data['COUNTRY'] = "United States";
				}
			}
			foreach($x->query("//div[@class='member-banner-tagline']//a") as $node)
			{
				$data['COMPANY_WEB_SITE'] = $node->getAttribute("href");
			}

			foreach($x->query("//a[@class='twitter']") as $node)
			{
				$data['TWITTER'] =  $node->getAttribute("href");
			}

			foreach($x->query("//a[@class='linkedin']") as $node)
			{
				$data['LINKED_IN'] =  $node->getAttribute("href");
			}

			foreach($x->query("//a[@class='google-plus']") as $node)
			{
				$data['GOOGLE_PLUS'] =  $node->getAttribute("href");
			}
			foreach($x->query("//a[@class='facebook']") as $node)
			{
				$data['FACEBOOK'] =  $node->getAttribute("href");
			}
			foreach($x->query("//div[@class='social-links social-link-sm']//a[last()]") as $node)
			{
				if($node->getAttribute("class") =="")
					$data['PERSONAL_WEB_PAGE'] =  $node->getAttribute("href");
			}
			
			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('SOURCE_URL')); 
		
		}
   }
}

$r= new inboud_com();
$r->parseCommandLine();

