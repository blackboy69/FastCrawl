<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class getapp extends baseScrape
{

   

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

     
      // cananda top 100 cities by population
      db::query("DELETE FROM raw_data where type = '$type' ");
	   db::query("DELETE FROM load_queue where type='$type' ");
//      db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");

      db::query("DROP TABLE $type ");
  //    db::query("UPDATE load_queue SET processing = 1 where type = '$type'  ");
 */
		for($i=1;$i<100;$i++)
		{
			$this->loadUrl("http://www.getapp.com/finance-accounting-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/business-intelligence-analytics-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/collaboration-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/customer-management-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/finance-accounting-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/hr-employee-management-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/it-communications-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/it-management-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/marketing-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/operations-management-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/project-management-planning-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/sales-software/?page=$i");
			$this->loadUrl("http://www.getapp.com/top-apps/?page=$i");
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


		if (preg_match("#page#",$url))
		{
			$urls = array();
			$x = new Xpath($html);
			foreach($x->query("//h2/a[@class='evnt']") as $node)
			{
				$urls[]= self::relative2Absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$x = new Xpath($html);

			foreach($x->query("//span[@itemprop='author']//preceding-sibling::div") as $node)
			{
				$data['WEB_SITE'] =  self::cleanup($node->textContent);
				break;
			}

			foreach($x->query("//h1") as $node)
			{
				$data["COMPANY"] = $node->textContent;
			}

			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('SOURCE_URL')); 
		
		}
   }
}

$r= new getapp();
$r->parseCommandLine();

