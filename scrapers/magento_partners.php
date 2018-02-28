<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class magento_partners extends baseScrape
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
      
		$this->loadUrl("http://partners.magento.com/partner_locator/search.aspx?l=All&r=All&t=All");
		//$this->loadUrl("http://partners.magento.com/partner_locator/partner_details.aspx?id=90283&backUrl=%2fpartner_locator%2fsearch.aspx%3fl%3dAll%26r%3dAll%26t%3dAll");
		
		$this->clean($reparse=true);

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


		if (preg_match("#partner_locator/search.aspx#",$url))
		{
			$urls = array();
			$x = new Xpath($html);
			foreach($x->query("//a[@class='partner_details']") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
			}	
			// next page links
			
			foreach($x->query("//div[@class='pagination']//a") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
			}

			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$x = new Xpath($html);
			$data =array();

			foreach($x->query("//a[@class='partnerName']") as $node)
			{
				$data['COMPANY_NAME'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//a[@id='GlobalMainContent_Company_WebsiteLink']") as $node)
			{
				$data['WEBSITE'] = $node->getAttribute("href");
				break;
			}


			foreach($x->query("//div[@id='GlobalMainContent_Company_Badge']") as $node)
			{
				$data['PARTNER_TIER'] = str_replace("badge badge_hostingpartners_","", $node->getAttribute('class'));
			}

			foreach($x->query("//div[contains(@class,'location location_position')]") as $node)
			{
				$x2=new Xpath($node);

				foreach($x2->query("//div[@class='title']") as $node2)
				{
					$title = self::cleanup($node2->textContent);
				}
				
				foreach($x2->query("//div[@class='address']") as $node2)
				{
					$data[$title]=$ap->parse($node2->textContent);
				}
			}

			// do a secondary scrape on the websites email and phone number
			if (!empty($data['WEBSITE']))
			{
				$websiteHtml = $thiz->get($data['WEBSITE']);

				$data = array_merge($data, $ep->parse(strip_tags($websiteHtml)));
				$data = array_merge($data, $pp->parse(strip_tags($websiteHtml)));
			}

			$data['SOURCE_URL'] = $url;
			log::info(db::normalize($data));
			db::store($type,$data,array('SOURCE_URL','CITY')); 
		}
   }
}

$r= new magento_partners();
$r->parseCommandLine();

