<?
include_once "config.inc";

class mindbodyonline3 extends baseScrape
{
	public static $mbCache = array();

   public static $_this=null;

	public function runLoader()
   {

		//R::freeze();
		$type= $table = get_class();		

		$this->threads=5;
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";
		$this->debug=false;

		$geos = db::query("SELECT city, max(lat) as lat ,max(lon) as lon from geo.locations where pop>25000 group by city order by pop desc",true);
		foreach($geos as $geo)
		{
			$city = $geo['city'];
			$lat = $geo['lat'];
			$lon = $geo['lon'];

			for($page=1;$page<=5;$page++)
			{
				//66.249.66.1 is googlebot https://support.google.com/webmasters/answer/80553?hl=en
				$webRequests[] = new WebRequest("https://clients.mindbodyonline.com/launch/search?lat=$lat&lon=$lon&page=$page",$type,"POST", "?term=&useLocation=true&page=$page&ip=66.249.66.1&debugModeOn=true&hasLatLon=true&lat=$lat&lon=$lon");
			}
		}
//		print_R($webRequests);
/*
		db::query("DELETE FROM load_queue  where type='$type'");
		db::query("DELETE FROM raw_data  where type='$type'");
		db::query("DROP TABLE $type");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");*/

		$this->loadWebRequests($webRequests);

		//prevetnt errors
		$data=array("STUDIOID"=>-1);
		db::store($type, $data, array("STUDIOID"));
	}

	function parse($url,$html)
	{
		log::info($url);

		$type = get_class();		
		$thiz = self::getInstance();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	

		$ap = new Address_Parser();	
		$pp = new Phone_Parser();	
		$ep = new Email_Parser();	


		if (preg_match("#clients.mindbodyonline.com/launch/search#",$url))
		{
			// load the listings page....
			$xTop =  new  XPath($html);

			foreach($xTop->query("//tr[@class='js-launch']") as $nodeTop)
			{
				$x = new Xpath($nodeTop);
				$studioid = $nodeTop->getAttribute("data-id");
				
				$data = db::query("SELECT * FROM $type WHERE STUDIOID = $studioid");
//				$data = array();
				$data["STUDIOID"] = $studioid;

				foreach($x->query("//*[@class='siteName']") as $node)
				{
					$data['COMPANY'] =  self::cleanup($node->textContent);
				}

				foreach($x->query("//*[@class='locationWrapper']") as $node)
				{
					$data = array_merge($data, $ap->parse($node->textContent));
				}								
				
				// LOAD THE CASPER JS
				$thiz->loadUrl("http://localhost/fastcrawl/casper.php?type=mindbodyonline&p1=$studioid");

				db::store($type, $data, array("STUDIOID"),true);
				log::info($data);
			}
			return;
		}
		else if (preg_match("#^http://localhost/fastcrawl/casper.php#",$url))
		{
			$studioid = $query['p1'];
			
			if (preg_match("#javascript:gotoStudioSite\('(.*?)'#",$html, $matches))
			{
				$data = db::query("SELECT * FROM $type WHERE STUDIOID = $studioid");
				$data["WEBSITE"] = $href = $matches[1];
				$data["STUDIOID"] = $studioid;				
				
				if (strpos($href, "?") === false)
					$href .="?studioid=$studioid";
				else
					$href .="&studioid=$studioid";

				$thiz->loadUrl($href);

				if (preg_match("#If you are unable to create a login, please contact .+? at (.+)\.#",$html,$matches))
				{
					$data['PHONE'] = $matches[1];
				}
				
				db::store($type, $data, array("STUDIOID"),true);
				log::info($data);
			}
			return;
		}
		else
		{

			// run the email and phone parser these
			$studioid = $query['studioid'];
			if ($studioid > 0)
			{
				$data = db::query("SELECT * FROM $type WHERE STUDIOID = $studioid");
				$data["STUDIOID"] = $studioid;

				list($data["WEBSITE"], $junk) = explode("?", $url);
				$x =  new  XPath($html);
				foreach($x->query("//body") as $node)
				{
					$data=array_merge($data,$ep->parse($node->textContent));
					if (empty($data['PHONE']))
						$data=array_merge($data,$pp->parse($node->textContent));
			
					db::store($type, $data, array("STUDIOID"),true);
					log::info($data);
				}

			}
		}

	return;
	}
}

$r = new mindbodyonline3();
$r->parseCommandLine();
