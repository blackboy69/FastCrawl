<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class demandware_com extends baseScrape
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

		$this->loadUrl("http://www.demandware.com/link-marketplace?BEGIN");
//		$this->loadUrl("http://www.exacttarget.com/exacttarget-partners/partner/clickmail");
		
$this->clean($reparse=true);
		//$this->loadUrl("https://www.google.com/analytics/partners/company/5987008988053504/gacp/5629499534213120/service/5724160613416960");
		//$this->loadUrl("http://inbound.org/members/all/hot?&per_page=48",$force=true);

	}

/*
   static $hostCount=array();
   static function loadCallBack($url,$html,$arg3)
   {
		$st = rand(1,10);
		log::info("Sleeping for a bit $st seconds");
      // we move a bit slower here.
		sleep($st);
      baseScrape::loadCallBack($url,$html,$arg3);
   }
*/

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
      parse_str(parse_url($url,PHP_URL_QUERY),$query); 

   // file_put_contents("$type.html",$html); 
      $webRequests = array();
      $links = array();
      $data = array();


		if (preg_match("#BEGIN#",$url))
		{
			$urls = array();
			$x = new Xpath($html);
				//*[@id="block-et-partner-partner-directory"]/div/div[3]/div/div

			foreach($x->query("//script[@id='marketplace-json']") as $node)
			{
				$locations = $json = json_decode($node->textContent,true);				
			}	
			
			foreach( $locations as $loc)
			{
				$urls[]=  "http://byronwhitlock.com/fastcrawl/casper.php?type=render&p1=".urlencode(self::relative2Absolute($url,$loc['page_uri']));
			}


			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$x = new Xpath($html);
			$data =array();

			foreach($x->query("//title") as $node)
			{
				$data['COMPANY_NAME'] = preg_replace("#\|.+#","", $node->textContent);
			}

			$regions = array();
			foreach($x->query("//h5[text()='Regions:']/following-sibling::p") as $node)
			{
				$regions[] = self::cleanup($node->textContent);
			}
			$data['LOCATION'] = join (", ",$regions);

			$cap = array();
			foreach($x->query("//h5[text()='Capabilities:']/following-sibling::ul//li") as $node)
			{
				$cap[] = self::cleanup($node->textContent);
			}
			$data['CAPABILITIES'] = join (", ",$cap);


			foreach($x->query("//h5[text()='Partner Type:']/following-sibling::ul") as $node)
			{
				$data['PARTNER_TYPE'] = self::cleanup($node->textContent);
			}
	
			foreach($x->query("//section[@class='partner-contact-details']/article[1]//a") as $node)
			{
				$data['WEBSITE'] = $node->getAttribute("href");
				break;
			}
			foreach($x->query("//section[@class='partner-contact-details']/article[2]") as $node)
			{
				$data['EMAIL'] = preg_replace("#/\*.+#", "", $node->textContent);
				break;
			}
			foreach($x->query("//section[@class='partner-contact-details']/article[3]") as $node)
			{
				$data['PHONE'] = $node->textContent;
				break;
			}



			$data['SOURCE_URL'] = $query['p1'];
			log::info(db::normalize($data));
			db::store($type,$data,array('SOURCE_URL')); 
		}
		
   }
}

$r= new demandware_com();
$r->parseCommandLine();

