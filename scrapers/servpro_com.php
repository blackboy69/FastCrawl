<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class servpro_com extends baseScrape
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

      db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' and url not like '%ervpro.com/search/locator%'");

      db::query("DROP TABLE $type ");
/*
     db::query("DELETE FROM raw_data where type = '$type' ");
	   db::query("DELETE FROM load_queue where type='$type' ");
		db::query("DROP TABLE $type ");
		db::query("UPDATE load_queue SET processing = 0 where  type = '$type' ");

		db::query("UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data  WHERE type = '$type') AND type = '$type' ");

		db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' ");
		db::query("DROP TABLE $type ");*/
		$this->loadUrlsByCity("https://www.servpro.com/search/locator?Address=123+mapel&City=%CITY%&State=%STATE%&ZipCode=%ZIP%");
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


		if (preg_match("#ervpro.com/search/locator#",$url))
		{
			$urls = array();
			$x = new Xpath($html);
			foreach($x->query("//a[contains(text(),'View Additional Certifications')]") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
				$host = parse_url($node->getAttribute("href"),PHP_URL_HOST);
				$host .="/contact/contactus";
				$urls[]=  $host;

			}	
			
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$x = new Xpath($html);
			$data =array();
			/// find the address....
			foreach($x->query("//address") as $node)
			{
				$data = array_merge($data,  $ap->parse(self::cleanup(strip_tags($node->c14n())) ));
			}
			
			$ra = db::quote($data['RAW_ADDRESS']);
			$owner = db::onecell("SELECT OWNER_NAME FROM $type where RAW_ADDRESS='$ra'");
			$email = db::onecell("SELECT EMAIL FROM $type where RAW_ADDRESS='$ra'");
			
			foreach($x->query("//title") as $node)
			{
				if (strpos($node->textContent,"|") > 0)
					list($junk, $data['COMPANY_NAME']) = explode("|", $node->textContent);
				else
					$data['COMPANY_NAME'] = $node->textContent;
			}
			
			
			if (empty($owner))
			{
				foreach($x->query("//*[contains(@class,'owner-photo')]//h2") as $node)
				{
					$data["OWNER_NAME"] = html_entity_decode(iconv('UTF-8', 'ASCII//TRANSLIT', self::cleanup(strip_tags($node->c14n()))));
				}
			}
			if (empty($email))
			{
				foreach($x->query("//a[contains(@href,'mailto')]") as $node)
				{
					$data = array_merge($data, $ep->parse(self::cleanup(strip_tags($node->textContent))));
					break;
				}
			}

			foreach($x->query("//*[@class='tel']") as $node)
			{
				$data['TELEPHONE'] = self::cleanup($node->textContent);
			}
		
			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('RAW_ADDRESS'),true); 
		
		}
   }
}

$r= new servpro_com();
$r->parseCommandLine();

