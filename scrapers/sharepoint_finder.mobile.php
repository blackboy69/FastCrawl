<?
include_once "config.inc";

class sharepoint_finder_mobile extends baseScrape
{
    public static $_this=null;
	public $timeout = 120;
	public $GoogleApiKey = "AIzaSyBCDTkd4kr8Lv4VQXmbJmPKy5QkXIp0rC8";//set this-
	
	public $SCREEN_SHOT_DIR = "../web/sharepoint_finder/iPhone";
	
	
		public $noGoogleBlock=true;
   public function runLoader()
   {
		
		$type = get_class();		

		$this->noProxy=true;

		$this->threads= 1;
	
		$domains = db::oneCol("SELECT DOMAIN from sharepoint_finder WHERE IP_ADDRESS is not null and IP_ADDRESS  != '' order by rand()");
		foreach($domains as $domain)
		{
			log::info("Pre-Loading {$domain}")			;
			$urls[] = "http://localhost:86/casper.php?type=render_sharepoint_finder_mobile&p1=".urlencode("https://www.google.com/webmasters/tools/mobile-friendly/?url=".$domain);
			//break;
		}
		$this->loadUrlsByArray($urls);	
	}
	
	static function parse($url,$html, $response_headers)
	{
		$x = new xpath($html);
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$url1=$query['p1'];
		
		parse_str(parse_url($url1,PHP_URL_QUERY),$q); // address and zip				
		$data['DOMAIN'] = preg_replace("#^www\.#","",parse_url($q['url'], PHP_URL_HOST));
		
		$data['GOOGLE_WEBMASTER_TOOLS_MOBILE_FRIENDLY']= $html;
		
		log::info($data);			
		db::store($type,$data, array("DOMAIN"));	
	
	}
}

$r = new sharepoint_finder_mobile();
$r->parseCommandLine();

