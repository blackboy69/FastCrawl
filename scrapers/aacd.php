<?
include_once "config.inc";

class aacd extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		

		//R::freeze();
		$type = get_class();		
		//db::query("DELETE FROM load_queue  where type='$type'");
		//db::query("DELETE FROM raw_data  where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");


		//db::query("DROP TABLE  $type");
		//db::query("DELETE FROM $type");
		
		db::query("		
		UPDATE load_queue
		 
		 SET processing = 0

		 WHERE			 
			 url IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) < 3000 And url like 'http://www.google.com%')
			 AND type ='$type'
		");

		
		db::query("		
		UPDATE load_queue
		 
		 SET processing = 1

		 WHERE			 
			 url  IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) > 1250)
			 AND type ='$type'
		");

		$this->threads=1;
//		$this->noProxy=true;

		$this->debug=false;

		$url = "http://aacd.com/index.php?module=aacd.websiteforms&cmd=memberreferral&comm=display&stype=&last_name=&state=&country=&zip=94928&radius=50&include_accr=1&area_code=&member_request=&include_add=1&member_class=&agreeTerms=1";
		$this->loadUrl($url);

		$url = "http://aacd.com/index.php?module=aacd.websiteforms&cmd=memberreferral&comm=display&stype=&last_name=&state=&country=&zip=%ZIP%&radius=50&include_accr=1&area_code=&member_request=&include_add=1&member_class=&agreeTerms=1";
		$this->loadUrlsByZip($url);

		$this->queuedGet();
   }

   public static function loadCallBack($url,$html,$type)
   {
	   static $numCalls = 1;

	   if ($numCalls > 20)
	   {
		   $numCalls=0;
		   self::getInstance()->switchProxy($url);
	   }

	   file_put_contents("lastload.html",$html);
	   if (strpos($html,"Abuse Detection System") || strlen($html) <  1250)
	   {
		   self::getInstance()->switchProxy($url);
	   }
	   else
	   {
		   parent::loadCallBack($url,$html,$type);
	   }
   }

	static function parse($url,$html)
	{
		$a = new Address_Parser();
		$p = new Phone_Parser();
		$oh = new Operating_Hours_Parser();

		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);

		$x = new HtmlParser($html);	
		$data = array();



		// get the list of salons
		//*[@id="Table3"]
		foreach ($x->query("//div/div/table/tr") as $node)
		{
			$listingHtml = $node->c14n();
			if ($node->getAttribute("height") != "")
			{
				continue;
			}
			
			$data = array();

			$x1 = new HtmlParser($listingHtml);	
			foreach( $x1->query("//td[@width='300']//a/b") as $nameNode)
			{
				$data['Name'] = $nameNode->textContent;
			}

			if (!isset($data['Name']))
				continue;

			foreach( $x1->query("//td[@width='280']//a[text()='View Map']") as $mapNode)
			{
				$href  = $mapNode->getAttribute("href");
				$query = array();
				parse_str(parse_url($href,PHP_URL_QUERY),$query);
				$data = array_merge($data, $a->parse($query['q']));	
			}
			
			foreach( $x1->query("//td[@width='280']//a[text()='Visit Website']") as $siteNode)
			{
				$href = self::relative2absolute($url, $siteNode->getAttribute("href"));
				$query = array();
				parse_str(parse_url($href,PHP_URL_QUERY),$query);
				$data['Url'] = $query['url'];
			}

			// grab the phone numbers
			$data = array_merge($data, $p->parse($listingHtml));	

			// cleanup
			log::info($data);
			db::store($type, $data,array('Name', 'Raw Address'),false);			
		}

		// next link?
		foreach( $x->query("//a[text()='Next >']") as $node)
		{
			$href = self::relative2absolute($url, $node->getAttribute("href"));
			self::getInstance()->loadUrl($href);
		}
	}


	static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/","",$str);
	}
	
	static function cleanup($str)
	{
		while (preg_match("/  /",$str))
		{
			$str = preg_replace("/  /i"," ",$str);
		}
		return trim($str);
	}

}

$r = new aacd();
$r->parseCommandLine();