<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class google_analytics_partners extends baseScrape
{

   

   public function runLoader()
   {
      $type = get_class();    

      //$this->maxRetries = 100;
      //$this->timeout = 15;
      $this->useCookies=true;
      $this->allowRedirects = true;
      $this->debug=false;
	    $this->threads=1;
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
      db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' and url not like '%ervpro.com/search/locator%'");

      db::query("DROP TABLE $type ");

     db::query("DELETE FROM raw_data where type = '$type' ");
	   db::query("DELETE FROM load_queue where type='$type' ");
		db::query("DROP TABLE $type ");
		db::query("UPDATE load_queue SET processing = 0 where  type = '$type' ");

		db::query("UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data  WHERE type = '$type') AND type = '$type' ");

		db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");
		db::query("DROP TABLE $type ");*/
		$this->loadUrl("https://www.google.com/analytics/partners/search/services");
		
		$this->clean($reparse=true);
		//$this->loadUrl("https://www.google.com/analytics/partners/company/5987008988053504/gacp/5629499534213120/service/5724160613416960");
		//$this->loadUrl("http://inbound.org/members/all/hot?&per_page=48",$force=true);

	}

/*
   static $hostCount=array();
   static function loadCallBack($url,$html,$arg3)
   {
      // we move very slow very very slow
		//sleep(10);
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


		if (preg_match("#google.com/analytics/partners/search/services#",$url))
		{
			$urls = array();
			$x = new Xpath($html);
			foreach($x->query("//h3//a") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
			}	
			// next page links
			
			foreach($x->query("//a[@class='pagination-link']") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
			}

			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$x = new Xpath($html);
			$data =array();

			foreach($x->query("//h1[@class='gap-listing-title']") as $node)
			{
				$data['COMPANY_NAME'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//div[contains(@class,'gap-listing-detail-visit-link')]//a") as $node)
			{
				$data['WEBSITE'] = $node->getAttribute("href");
				break;
			}

			foreach($x->query("//input[@name='star_rating']") as $node)
			{
				$data['AVG_RATING'] = $node->getAttribute('value');
			}
			foreach($x->query("//span[@class='gap-rating-count']") as $node)
			{
				$data['NUM_REVIEWS'] = preg_replace("/([^0-9]+)/","", $node->textContent);
			}
			

			foreach($x->query("//div[@class='appgall-list-locations']//div[contains(@class,'appgall-item')]") as $node)
			{

				list($junk,$location,$data['PHONE'],$data['EMAIL'],$junk) =  explode("\n", $node->textContent); 
				list($data['CITY'],$data['COUNTRY'],$data['STATE']) = explode(", ",$location);
				$data['SOURCE_URL'] = $url;
				log::info(db::normalize($data));
				db::store($type,$data,array('SOURCE_URL','CITY')); 
			}
		}
   }
}

$r= new google_analytics_partners();
$r->parseCommandLine();

