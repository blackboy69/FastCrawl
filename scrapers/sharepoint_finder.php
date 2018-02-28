<?
include_once "config.inc";

class sharepoint_finder extends baseScrape
{
    public static $_this=null;
	public $timeout = 120;
	public $GoogleApiKey = "AIzaSyBCDTkd4kr8Lv4VQXmbJmPKy5QkXIp0rC8";//set this-
	
	public $SCREEN_SHOT_DIR = "../web/sharepoint_finder/iPhone";
	
	
		public $noGoogleBlock=true;
   public function runLoader()
   {
		
		$type = get_class();		
/*
		db::query("		
			UPDATE load_queue
			 
			 SET processing = 0

			 WHERE			 
				 url IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) < 3000 And url like 'http://www.google.com%')
				 AND type ='$type'
		");*/

		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");

*/
		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		//db::query("DROP TABLE $type");	
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
//..		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='sharepoint_finder' ");
//		

		//$this->noProxy=true;

//
/*
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");
		

		*/
		//db::query("UPDATE load_queue set processing=1 where type='$type' and processing=0");
		//db::query("UPDATE raw_data set parsed=1 where type='$type' and parsed=0");
		$this->threads= 10;
		
		$this->sleepTime['GOOGLE_API']=0;
		
		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		//return;
		//db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
	
		// topsharepoint.com has lots of sites. start here.
		$this->loadUrl("http://www.topsharepoint.com/category/top-sites");
		
		// also load any urls from the csv file...
		
		$table = arrayFromCSV("inputdata/BuiltWith - SP 2010.csv",$hasFieldNames = true);
		$table = array_merge($table, arrayFromCSV("inputdata/BuiltWith - SP Office.csv",$hasFieldNames = true));
		
		foreach($table as $bigdata)
		{
			// change domain field 
			$locations = explode(";",$bigdata['Location on Site']);
			$data = $bigdata;
			
			unset ($data['Location on Site']);
			foreach($locations as $loc)
			{
				
				try
				{
					$data = db::normalize($data);
					$data['DOMAIN'] = preg_replace("#^www\.#","",preg_replace("#/\*#","",$loc));
					log::info("Pre-Loading {$data['DOMAIN']}")			;
					$urls[] = "http://".$data['DOMAIN'];
					//$urls[]= "https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url=http://{$data['DOMAIN']}&screenshot=false&key=$this->GoogleApiKey";
					db::store($type,$data, array("DOMAIN"),$overwrite=false);
					//
					
				}
				catch (Exception $ex)
				{
					log::info($ex);
				}
			}
			
		}
		$this->loadUrlsByArray($urls);	
	}
	
	static function parse($url,$html, $response_headers)
	{
		$x = new xpath($html);
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		if (preg_match("#www.topsharepoint.com/category/top-sites#", $url))
		{			
			$hrefs = array();
			// load next page links
			foreach($x->query("//div[@class='wp-pagenavi']//a") as $node)
			{
				$hrefs[] = self::relative2absolute($url,$node->getAttribute("href")) ;					
			}
			
			// load the individual urls
			foreach($x->query("//h1[@class='post-title']//a") as $node)
			{
				$hrefs[] = self::relative2absolute($url,$node->getAttribute("href")) ;					
			}			
			$thiz->loadUrlsByArray($hrefs);
			return;
		}
		else if (preg_match("/localhost/",$url))
		{
			// do nothing
		}
		else if (preg_match("/www.googleapis.com/",$url)) // screenshot/ screen stats
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
			$data['GOOGLE_PAGESPEED_RESPONSE_TIME_MESSAGE'] =$json["formattedResults"]["ruleResults"]["MainResourceServerResponseTime"]['urlBlocks'][0]['header']['format'];
			$data['GOOGLE_PAGESPEED_RESPONSE_TIME_MESSAGE_DATA'] =json_encode($json["formattedResults"]["ruleResults"]["MainResourceServerResponseTime"]['urlBlocks'][0]['header']['args']);
			
			
			
			$data['GOOGLE_PAGESPEED_MINIFY_CSS'] =$json["formattedResults"]["ruleResults"]["MinifyCss"]['ruleImpact'];
			$data['GOOGLE_PAGESPEED_MINIFY_HTML'] =$json["formattedResults"]["ruleResults"]["MinifyHTML"]['ruleImpact'];

			//Eliminate render-blocking JavaScript and CSS in above-the-fold content
			$data['GOOGLE_PAGESPEED_MINIFY_BLOCKING'] =$json["formattedResults"]["ruleResults"]["MinimizeRenderBlockingResources"]['ruleImpact'];
			$data['GOOGLE_PAGESPEED_MINIFY_BLOCKING_MESSAGE'] =$json["formattedResults"]["ruleResults"]["MinimizeRenderBlockingResources"]['summary']['format'];
			$data['GOOGLE_PAGESPEED_MINIFY_BLOCKING_MESSAGE_DATA'] = json_encode($json["formattedResults"]["ruleResults"]["MinimizeRenderBlockingResources"]['summary']['args']);
			
			// Prioritize visible content
			$data['GOOGLE_PAGESPEED_Prioritize_Visible_Content_IMPACT'] =$json["formattedResults"]["ruleResults"]["PrioritizeVisibleContent"]['ruleImpact'];
			$data['GOOGLE_PAGESPEED_Prioritize_Visible_Content_MESSAGE'] =$json["formattedResults"]["ruleResults"]["PrioritizeVisibleContent"]['summary']['format'];
			$data['GOOGLE_PAGESPEED_Prioritize_Visible_Content_MESSAGE_DATA'] = json_encode($json["formattedResults"]["ruleResults"]["PrioritizeVisibleContent"]['summary']['args']);

			$data['GOOGLE_PAGESPEED_OPTIMIZE_IMAGES'] = $json["formattedResults"]["ruleResults"]["OptimizeImages"]['ruleImpact'];
				
			log::info($data);			
			db::store($type,$data, array("DOMAIN"));	
			
		}
		
		else 
		{
			// this is the site, lets start doing some work.
			$data = array();
			
			$data['DOMAIN'] = preg_replace("#^www\.#","",parse_url($url, PHP_URL_HOST));

			// Site URL
			$data['SITE_URL'] = $url;
			foreach($x->query("//title") as $node)
			{
				$data['TITLE'] = self::cleanup($node->textContent);
			}
			
			if (isset($response_headers['MicrosoftSharePointTeamServices']))
			{
				$data['SHAREPOINT_VERSION'] = $response_headers['MicrosoftSharePointTeamServices']; // first check header..
			}
			$domain = urlencode($data['DOMAIN']);
			
			if (isset($data['SHAREPOINT_VERSION'] ))
			{
				//screen shots
				//$this->loadUrl("http://localhost:86/casper.php?type=render_ipad&p1=http://$domain&p2=$domain");
				$thiz->loadUrl("http://localhost:86/casper.php?type=render_iphone&p1=http://$domain&p2=$domain");
				//$this->loadUrl("http://localhost:86/casper.php?type=render_pc&p1=http://$domain&p2=$domain");
				// use google for screen shots instead
				
				$ip = db::oneCell("SELECT IP_ADDRESS from $type where DOMAIN = '$domain'");
				
				if (empty($ip)) // don't do this twice
				{
					$thiz->loadUrl("https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url=http://{$data['DOMAIN']}&screenshot=false&key=$thiz->GoogleApiKey");
					// geocode the ip address..
					$ip = $data['IP_ADDRESS']=gethostbyname($domain);
					// $this->loadUrl("http://ipinfo.io/$ip/geo");
					
					//dynamic check
					$data['WEB_PARTS'] = 'STATIC';
					if (preg_match("/s4-wpcell-plain/", $html))
						$data['WEB_PARTS'] = 'DYNAMIC';
					
					//security
					$security_url = "http://$domain//_layouts/viewlsts.aspx";
					$furl = $thiz->getFinalAddress($security_url);
					
					if ($thiz->response['http_code'] == 200)
						$data['APPLICATION_PAGE_SECURITY'] = "NO (200)";
					else if ($thiz->response['http_code'] == 401)
						$data['APPLICATION_PAGE_SECURITY'] = "YES (401)";
					else
						$data['APPLICATION_PAGE_SECURITY'] = "UNKNOWN ({$thiz->response['http_code']})";
								
					// windows auth
					$furl = $thiz->getFinalAddress($security_url);
					if (preg_match("#_windows/default.aspx#",$furl))
						$data['WINDOWS_AUTH_ONLY'] = "YES";
					else
						$data['WINDOWS_AUTH_ONLY'] = "NO";
									
					
					// SEO 
					if (preg_match("/.aspx/",$url))
						$data['SEO_FREINDLY']="NO";
					else
						$data['SEO_FREINDLY']="YES";
					
					
					// meta tags
					$meta=getMetaTags($html);			
					$data['META_TAGS_JSON'] = json_encode($meta);
					//$data['DOWNLOAD_TIME'] = $response_headers['total_time'];
					
					
					// build numbers can be referenced here:
							/*v16 SP 2016 https://blogs.technet.microsoft.com/steve_chen/sharepoint-2016-build-numbers/
					v15 SP 2013 https://blogs.technet.microsoft.com/steve_chen/sharepoint-2013-build-numbers-and-cus/
					v14 SP 2010 https://blogs.technet.microsoft.com/steve_chen/sharepoint-2010-build-numbers-cube-sheet/
					v12 SP 2007 https://blogs.technet.microsoft.com/steve_chen/builds-and-updates-mosswss/
					<v11 SP 2001-2005
			
					$version[12] = "SharePoint Portal Server 2001"
					SharePoint Team Services (2002)
					SharePoint Services 2.0 (free license) - SharePoint Portal Server 2003 (commercial release)
					*/
					
						
					db::store($type,$data, array("DOMAIN"));	
					log::info($data);		
				}
			}		
		}
		
	}
}

function getMetaTags($str)
{
  $pattern = '
  ~<\s*meta\s

  # using lookahead to capture type to $1
    (?=[^>]*?
    \b(?:name|property|http-equiv)\s*=\s*
    (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
    ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
  )

  # capture content to $2
  [^>]*?\bcontent\s*=\s*
    (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
    ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
  [^>]*>

  ~ix';
  
  if(preg_match_all($pattern, $str, $out))
    return array_combine($out[1], $out[2]);
  return array();
}

//db::query("UPDATE  raw_data set parsed=0 where type='sharepoint_finder' and parsed=1 ");
/*db::query("DROP TABLE sharepoint_finder ");
*/
$r = new sharepoint_finder();
$r->parseCommandLine();

