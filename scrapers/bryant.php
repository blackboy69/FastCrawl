<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class bryant extends baseScrape
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
//    $this->threads=2;
// $this->debug=true;
   //log::error_level(ERROR_DEBUG_VERBOSE);
      
      /*
      db::query("
      UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)      
          ");

      */
      // cananda top 100 cities by population
      db::query("DELETE FROM raw_data where type = '$type' ");
	    db::query("DELETE FROM load_queue where type='$type' and url like '%find-a-cpa' ");
      db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");

      db::query("DROP TABLE $type ");
      //db::query("UPDATE load_queue SET processing = 1 where type = '$type'  ");

      $webRequests= array();
//    $this->noProxy= false;
   // $this->proxy = "localhost:8888";

      $html = $this->loadUrlsByZip("?");

		$result = mysql_query("SELECT distinct zip,state FROM geo.locations order by pop desc LIMIT 2000") ;
      while ($r = mysql_fetch_row($result))
      {
			$zip = $r[0];
			$url = "http://www.bryant.com/pros/index.shtml?$zip";
			$webRequests[] = new WebRequest($url,$type,"POST","searchBrand=BR&zipcode=%ZIP%&submit.x=18&submit.y=6&submit=Search");
        
      }
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

		$xTop =  new  XPath($html);	
		foreach($xTop->query("//div[contains(@id='details')]") as $nodeTop)
		{
			$x = new Xpath($nodeTop);

			foreach($x->query("//h2") as $node)
			{
				$data['COMPANY'] =  self::cleanup($node->textContent);
			}

			foreach($x->query("//p[@class='address']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->textContent));
			}

			$data = array_merge($data,$pp->parse($nodeTop->textContent));
			$data = array_merge($data,$ep->parse($nodeTop->textContent));

			$data['COUNTRY'] = 'United States';
			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('SOURCE_URL')); 
		}	
   }
}

$r= new bryant();
$r->parseCommandLine();

