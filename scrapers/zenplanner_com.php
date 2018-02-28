<?
include_once "config.inc";

class zenplanner_com extends baseScrape
{
    public static $_this=null;
	public static $google = null;
	public static $bing = null;	
		public static $yahoo = null;	
	public static $csv=null;
	public static $csvMap=array();

	private $urlsToLoad = array();
	function __construct()
	{
			$type = get_class();
		parent::__construct();
		self::$bing = new search_engine_bing();
		self::$yahoo = new search_engine_yahoo();
		self::$google = new search_engine_google();
		$this->noProxy=true;
//		$this->switchProxy();

		//$this->nextProxyUrl = "http://hidemyass.com/proxy-list/search-225371"; // USA ONLY. required for bing results to be accurate when doing proxy jumping
//		$this->clean(false);

//		db::query("DELETE FROM Raw_data where type='$type' and url like '%?id=%' ");
	}

	
   public function runLoader()
   {
		$type = get_class();
//		db::query("DELETE FROM LOAD_QUEUE where type='$type'");
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
		


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		
*/
		//$this->proxy = "localhost:9666";


		// db::query("DELETE FROM load_queue where type='$type'");
//		db::query("DELETE FROM Raw_data where type='$type'");
//		db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
//				db::query("DROP TABLE $type");	
//$this->proxy="locahost:9666";
		$this->noProxy=true;
		$this->threads=1;
		$this->useCookies = true;
		$this->timeout = 15;
		$urlsToLoad = array();
		$this->maxRetries = 3;
		
		$urls = array();
	  $result = mysql_query("
			SELECT 
				geo.us_cities.city as CITY, 
				geo.us_cities.state AS STATE,
				MAX( geo.locations.zip ) as ZIP ,  
				max(lat) as LAT ,
				max(lon) as LON
			FROM  geo.us_cities 
			INNER JOIN geo.locations ON (geo.us_cities.city = geo.locations.city AND geo.US_CITIES.STATE  = GEO.locations.state)
			GROUP BY geo.us_cities.city, geo.us_cities.state 
			ORDER BY geo.us_cities.POP DESC
			limit 100
		");
		$urls = array();
//		$urls[] = self::$google->url("site:lightspeedwebstore.com San Francisco");
		
		//$urls[] = self::$bing->url("ite:lightspeedwebstore.com /site/login");
//		$urls[] = self::$bing->url("site:lightspeedwebstore.com");
		

		while ($r = mysql_fetch_row($result))
      {
			$city=$r[0];
			$state = $r[1];
			$zip = sprintf("%05d", $r[2]);
			$lat = sprintf("%.15f", $r[3]);
			$lon = sprintf("%.15f", $r[4]);

			$urls[] = self::$bing->url("site:zenplanner.com $city");

			
		// $urls[] = self::$google->url("site:sites.zenplanner.com  $city, $state");
		}
//		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1 ");
		
		foreach (range('A', 'Z') as $letter) {
			$this->loadUrl("http://byronwhitlock.com/fastcrawl/casper.php?type=render&&&p1=".urlencode(self::$bing->url("site:sites.zenplanner.com $letter",true)),true);
		}
//		$urls = array("https://traviscountystrength.sites.zenplanner.com/location.cfm");
//		$urls = array("https://cfemc.sites.zenplanner.com/location.cfm");
		//$this->loadUrlsByArray($urls);
					
		 //grab the recon-ng supplemental data
		// $this->loadUrlsByArray(file("$type.txt"));

		//$this->loadUrl("http://byronwhitlock.com/fastcrawl/casper.php?type=render&&p1=".urlencode(self::$bing->url("site:sites.zenplanner.com",true)),true);
	}
	

	static function parse($url,$html)
	{

		if (preg_match("#byronwhitlock.com/#", $url))
		{
	
			parse_str(parse_url($url,PHP_URL_QUERY),$bw); // address and zip
			log::info("Found Fastcrawl Proxy, updating $url => {$bw['p1']}");
			$url = $bw['p1'];

		}

		$t = $thiz = self::getInstance();
		$type = get_class();		
		$host = parse_url($url,PHP_URL_HOST);

		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$ep = new Email_Parser();
		
		log::info($url);
//										log::info($host);
		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip
		
		if (preg_match("/bing|google/",$host))
		{
			//if (empty($query['start']) || $query['start'] > 500) // only the first 50 pages
			{
				$toLoad=array();
				$hrefs = self::$bing->parse($html);			
				foreach ($hrefs as $href)
				{
					$hrefHost = parse_url($href,PHP_URL_HOST); 
					if (preg_match("/zenplanner.com/",$hrefHost))
					{						
						$toLoad[] = "http://byronwhitlock.com/fastcrawl/casper.php?type=render&&p1=".urlencode("http://$hrefHost/location.cfm");
					}
					else if (preg_match("/bing.com/",$hrefHost)) // next page links
					{
							$toLoad[] = "http://byronwhitlock.com/fastcrawl/casper.php?type=render&fresh=3&p1=".urlencode($href);
					}
					else 
						$toLoad[] = "http://byronwhitlock.com/fastcrawl/casper.php?type=render&&p1=".urlencode($href);
				}
				log::info($toLoad);
				self::getInstance()->loadUrlsByArray($toLoad);
			}
		}
		/*else if (preg_match("/google/",$host))
		{
			$urls = self::$google->parse($html,true);
			log::info($urls);
			return;
			self::getInstance()->loadUrlsByArray($urls);
		}
		else if (preg_match("/yahoo/",$host))
		{
			self::getInstance()->loadUrlsByArray(self::$yahoo->parse($html));
		}*/
		else if (preg_match("#zenplanner.com#",$host))
		{
			$data = array();
			$x = new  XPath($html);	

/*			
    google.maps.event.addListener(marker1, 'click', function() {
				    	    infoWindow1.open(map, marker1);
					    });
					    
					    var infoDiv1 = 'ottom:20px;\"><div class=\"bold\">Main Location</div><div class=\"adr\"><div class=\"street-address\">2687 S. Preston St.</div><div class=\"extended-address\"></div><span class=\"locality\">Salt Lake City</span>,<span class=\"region\">UT</span><span class=\"postal-code\">84106</span></div><a href=\"calendar.cfm?LocationId=4CA91306-C511-41F5-853F-C5D3D478B3BA\" class=\"plain spaceAbove\"><img src=\"skin/calendar.png\" width=\"16\" height=\"16\" align=\"absmiddle\"><span class=\"hyperlink\">Calendar For This Location</span></a></div>';
					    
	*/
			foreach($x->query("//title") as $node)
			{
				$data['COMPANY_NAME'] = self::cleanup($node->textContent);
			}


			if (preg_match("#var infoDiv1 = '(.+)';#",$html, $matches))
			{
				$locHtml = stripslashes($matches[1]);

				$locX = new Xpath($locHtml);

				$locations = array();
				foreach($locX->query("//div[@class='adr']") as $node)
				{
					$data= array_merge($data, $ap->parse($node->c14n()));
					break;
				}
			}

			foreach($x->query("//div[@id='idHeader']//a") as $node)
			{
				$data['WEBSITE'] = $node->getAttribute("href");
			}		
				



			unset($data['RAW_ADDRESS']);
			$data['SOURCE_URL'] = $url;
			log::info($data);
			$id = db::store($type,$data,array('COMPANY_NAME', 'ADDRESS','CITY','SOURCE_URL'),true);

			if (!empty($data['WEBSITE']))
			{
				$thiz->loadUrl($data['WEBSITE']."?id=$id");
			}
		}
		else // check the id
		{
			if (!empty($query['id']))
			{
				$id = $query['id'];

				$data = db::query("SELECT * FROM $type where id = $id");
				
				$data=array_merge($data,$ep->parse(strip_tags($html)));
				$data=array_merge($data,$pp->parse(strip_tags($html)));
				
				log::info("!!!SECONDARY PARSE!!!!");

				// did we find email or phone numbers?
				if ( isset($data['EMAIL']) || isset($data['PHONE']) )
				{				
					log::info($data);
					db::store($type,$data,array('COMPANY_NAME', 'ADDRESS','CITY','SOURCE_URL'),true);
				}
				// otherwise spider to the contact us page when both aren't already set.
				else if (! (isset($data['EMAIL']) && isset($data['PHONE'])) )
				{
					$x = new  XPath($html);	

					foreach($x->query("//a[contains( translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'contact')]") as $node)
					{
						$href = self::relative2absolute($url,$node->getAttribute("href")."?id=$id");
						
						log::info("Found Contact us page");
						log::info($href);
						$thiz->loadUrl($href);
					}
				}
			}
			else
			log::info("Unknown url");

		}
	}

}
$r = new zenplanner_com();
$r->parseCommandLine();
