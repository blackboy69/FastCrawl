<?
include_once "config.inc";

class lightspeedwebstore_com extends baseScrape
{
	public static $mbCache = array();

   public static $_this=null;

	public function runLoader()
   {

		//R::freeze();
		$type= $table = get_class();		

		$this->threads=1;
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";
		$this->debug=false;
		$this->timeout=0;//never timeout http connections

		
//		print_R($webRequests);
/*
		db::query("DELETE FROM load_queue  where type='$type'");
		db::query("DELETE FROM raw_data  where type='$type'");
		db::query("DROP TABLE $type");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");*/

		$this->loadUrl("http://byronwhitlock.com/fastcrawl/casper.php?type=lightspeedwebstore_com&p1=site:lightspeedwebstore.com&p2=San+Francisco,CA");
	}

	function parse($url,$html)
	{
		return;
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
				$thiz->loadUrl("http://localhost/fastcrawl/casper.php?type=$type&p1=$studioid");

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

$r = new lightspeedwebstore_com();
$r->parseCommandLine();

