<?
include_once "config.inc";

class sharepoint_finder_test extends baseScrape
{
    public static $_this=null;
	public $timeout = 120;
	public $GoogleApiKey = "AIzaSyBCDTkd4kr8Lv4VQXmbJmPKy5QkXIp0rC8";//set this-
	
	public $SCREEN_SHOT_DIR = "../web/sharepoint_finder/Google";
		
   public function runLoader()
   {
		
		$type = get_class();	
		
		$data['DOMAIN'] = "http://www.topsharepoint.com/";
		$this->loadUrl("https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url={$data['DOMAIN']}&screenshot=true&key=$this->GoogleApiKey");
		
		$data['DOMAIN'] = "http://www.scrapeforce.com/";
		$this->loadUrl("https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url={$data['DOMAIN']}&screenshot=true&key=$this->GoogleApiKey");
		
		$data['DOMAIN'] = "http://www.monster.com/";
		$this->loadUrl("https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url={$data['DOMAIN']}&screenshot=true&key=$this->GoogleApiKey");
		
		
		
	}
	
	static function parse($url,$html, $response_headers)
	{
		
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		
		$data = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$q); // address and zip				
		$data['DOMAIN'] = preg_replace("#^www\.#","",parse_url($q['url'], PHP_URL_HOST));
			
		
		$json = json_decode($html,true);
		$data['GOOGLE_PAGESPEED_SCORE']=$json["ruleGroups"]["SPEED"]['score'];
		
		//Your page has {{NUM_REDIRECTS}} redirects. Redirects introduce additional delays before the page can be loaded
		$data['GOOGLE_PAGESPEED_AVOID_LANDING_PAGE_REDIRECTS_IMPACT'] =$json["formattedResults"]["ruleResults"]["AvoidLandingPageRedirects"]['ruleImpact'];
		$data['GOOGLE_PAGESPEED_AVOID_LANDING_PAGE_REDIRECTS_MESSAGE'] =$json["formattedResults"]["ruleResults"]["AvoidLandingPageRedirects"]['summary']['format'];
		$data['GOOGLE_PAGESPEED_AVOID_LANDING_PAGE_REDIRECTS_MESSAGE_DATA'] =json_encode($json["formattedResults"]["ruleResults"]["AvoidLandingPageRedirects"]['summary']['args']);
		
		//Compressing resources with gzip or deflate can reduce the number of bytes sent over the network.
		$data['GOOGLE_PAGESPEED_GZIP_IMPACT'] =$json["formattedResults"]["ruleResults"]["EnableGzipCompression"]['ruleImpact'];
		$data['GOOGLE_PAGESPEED_GZIP_MESSAGE'] =$json["formattedResults"]["ruleResults"]["EnableGzipCompression"]['summary']['format'];
		
		
		//Setting an expiry date or a maximum age in the HTTP headers for static resources instructs the browser to load previously downloaded resources from local disk rather than over the network
		$data['GOOGLE_PAGESPEED_BROWSER_CACHE_IMPACT'] =$json["formattedResults"]["ruleResults"]["LeverageBrowserCaching"]['ruleImpact'];
		$data['GOOGLE_PAGESPEED_BROWSER_CACHE_MESSAGE'] =$json["formattedResults"]["ruleResults"]["LeverageBrowserCaching"]['summary']['format'];
		
		$data['GOOGLE_PAGESPEED_RESPONSE_TIME_IMPACT'] =$json["formattedResults"]["ruleResults"]["MainResourceServerResponseTime"]['ruleImpact'];
		$data['GOOGLE_PAGESPEED_RESPONSE_TIME_MESSAGE'] =$json["formattedResults"]["ruleResults"]["MainResourceServerResponseTime"]['summary']['format'];
		$data['GOOGLE_PAGESPEED_RESPONSE_TIME_MESSAGE_DATA'] =json_encode($json["formattedResults"]["ruleResults"]["MainResourceServerResponseTime"]['summary']['args']);
		
		
		$data['GOOGLE_PAGESPEED_MINIFY_CSS'] =$json["formattedResults"]["ruleResults"]["MinifyCss"]['ruleImpact'];
		$data['GOOGLE_PAGESPEED_MINIFY_HTML'] =$json["formattedResults"]["ruleResults"]["MinifyHTML"]['ruleImpact'];

		//Eliminate render-blocking JavaScript and CSS in above-the-fold content
		$data['GOOGLE_PAGESPEED_MINIFY_BLOCKING'] =$json["formattedResults"]["ruleResults"]["MinimizeRenderBlockingResources"]['ruleImpact'];
		$data['GOOGLE_PAGESPEED_MINIFY_BLOCKING_MESSAGE'] =$json["formattedResults"]["ruleResults"]["MinimizeRenderBlockingResources"]['summary']['format'];
		$data['GOOGLE_PAGESPEED_MINIFY_BLOCKING_MESSAGE_DATA'] = json_encode($json["formattedResults"]["ruleResults"]["MinimizeRenderBlockingResources"]['summary']['args']);

		$data['GOOGLE_PAGESPEED_OPTIMIZE_IMAGES'] = $json["formattedResults"]["ruleResults"]["OptimizeImages"]['ruleImpact'];
			
		log::info($data);			
		db::store($type,$data, array("DOMAIN"));	
	
	}
}

$r = new sharepoint_finder_test();
$r->parseCommandLine();

