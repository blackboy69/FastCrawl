<?php

include_once "config.inc";

class bbb_auto extends baseScrape
{
	public static $_this=null;
	public static $numCalls = 0;
	public $proxies = array();

	public function runLoader()
	{
		
		$type = get_class();		

		// db::query("DELETE FROM load_queue where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		//db::query("UPDATE load_queue set processing = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type'");
		#db::query("DROP TABLE  $type");
		//db::query("DELETE FROM $type");

		$this->maxRetries=100;
		$this->threads=6;
		$this->noProxy=false;
		$this->proxy = "198.52.128.247:80";
		//self::getInstance()->switchProxy("");

	//		$this->proxy = "localhost:8888";

		$this->timeout = 10;
		$this->debug=false;	
//		db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type' and url = 'http://www.bbb.org/phoenix/business-reviews/auto-repair-and-service/wilson-s-auto-repair-in-flagstaff-az-1000020056' ");

		db::query("DROP TABLE $type ");
		db::query("UPDATE raw_data SET parsed = 0 where parsed = 1  AND type = '$type'");
		$urls = array();
		for($i=1;$i<900;$i++)
		{
			$urls[] = "http://www.bbb.org/search/?type=name&input=Auto&location=Oakland%2c+CA&filter=business&accredited=accredited&page=$i&radius=50000";
		}
		
		//$urls[] = "http://www.bbb.org/atlantic-provinces/business-reviews/tire-dealers/city-tire-and-auto-centre-in-st-johns-nl-28068";
		$this->loadUrlsByArray($urls);


	}
	
   // basic load call back to populate the db.
   public static function loadCallBack($url,$html,$type)
   {

		$thiz =self::getInstance();
	   if (strpos($html,"we had to limit your access to this website") || strlen($html) < 15000 )
	   {		 			
			$html=null;
			log::info("Sorry, we had to limit your access to this website. ".strlen($html) );
			//$thiz->switchProxy($url);

	   }
	   //if (!strpos($html,"ipsecure") )
	   {		 			
			parent::loadCallBack($url,$html,$type);
		}
	//	else
		//	self::getInstance()->switchProxy($url);
		
	   
   }


	static function parse($url,$html)
	{
		if (strpos($url, "bbb.org/search/?"))
		{
		 self::parseListings($url,$html);
			//log::info("Not loading listings, for faster reparse");
		}
		else if (strpos($url, "business-reviews"))
		{
			self::parseDetails($url,$html);
		}
		else
		{
			log::info("Cannot Parse. Unknown URL: $url");
		}
	}

	static function parseListings($url,$html)
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

	//	file_put_contents("$type.html",$html);	
		$webRequests = array();
		$links = array();
		$data = array();
		$x =  new  XPath($html);	
		foreach ($x->query("//a[contains(@href, 'business-reviews')]") as $node)
		{
			if (! preg_match("/add-review|get-a-quote/",$node->getAttribute("href")))
				$links[] = self::relative2absolute($url, $node->getAttribute("href"));
		}
		if (!empty($links))
			$thiz->loadUrlsByArray($links);

		return false;

	}

	static function parseDetails($url,$html)
	{
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$html = str_replace("<br />","\n",$html);


		$html = str_replace("&nbsp;"," ",$html);
		$html = str_replace("\t"," ",$html);

		$type = get_class();		
		$thiz = self::getInstance();

		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$kvp = new KeyValue_Parser();	
		log::info($url);
		//parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		//	file_put_contents("$type.html",$html);	
		$data = array();
		$x =  new  XPath($html);	
			

		
		foreach ($x->query("//h1[@class='business-title']") as $node)
		{	
			$data['Name'] = self::reduceExtraSpaces($node->textContent);
		}

		if (isset($data['Name']))
		{
			$ap = new Address_Parser();
			foreach ($x->query("//span[@class='business-phone']") as $node)
			{	
				$data['Telephone'] = str_replace("\n",", ", str_replace("Phone: ", "", $node->textContent));
			}


			foreach ($x->query("//span[@class='business-fax']") as $node)
			{	
			//	$data['Fax'] = str_replace("Fax: ", "", $node->textContent);
			}
			
			foreach ($x->query("//span[@class='business-email']//a") as $node)
			{	
				$data['Email'] = trim($node->textContent);
			}
			$address = array();
			foreach ($x->query("//span[@class='business-address']//span") as $node)
			{				
				$address[] = $node->textContent;
			}
			$data = array_merge($data,$ap->parse( join(", ", $address) ));

			foreach ($x->query("//span[@class='business-link']//a") as $node)
			{	
				$data['Url'] = $node->getAttribute("href");
			}

			foreach ($x->query("//div[@id='complaint-sort-container']") as $node)
			{	
				if (preg_match("/complaints/i",$node->textContent))
					list($data['NUM_COMPLAINTS'], $junk) =  explode(" ", self::cleanup($node->textContent));

				if (preg_match("/reviews/i",$node->textContent))
					list($data['NUM_REVIEWS'], $junk) =  explode(" ",self::cleanup($node->textContent));
			}

			foreach ($x->query("//div[@id='accedited-rating']//img") as $node)
			{	
				$title = $node->getAttribute("title");
				if (empty($title))
					$data['BBB Rating'] =  $node->getAttribute("alt");
			}

			
			foreach ($x->query("//div[@id='accedited-rating']//span[@itemprop='ratingValue']") as $node)
			{	
				$data['BBB Rating Value'] = $node->textContent;
			}
			
			foreach ($x->query("//div[@id='business-additional-info-text']//span") as $node)
			{
				$span = $node->textContent;
				if (preg_match("/^(.+):(.+)/",$span,$matches))
				{
					$k = strtoupper(trim($matches[1]));

					if (strpos($k,":")>0)
						continue;

					if (strlen($k)<20)
						$data[$k] = trim($matches[2]);
				}
			}
			
			$contacts = 1;

			foreach ($x->query("//span[@class='employees']//span[@itemtype='http://schema.org/Person']") as $node)
			{	

				$x2 = new HtmlParser($node->c14n());	

				foreach($x2->query("//span[@itemprop='name']") as $nameNode)
				{
					$name = $nameNode->textContent;
				}

				foreach($x2->query("//span[@itemprop='jobTitle']") as $jobTitleNode)
				{
					$jobTitle = trim(preg_replace("/[^0-9a-zA-Z_ ]/","", $jobTitleNode->textContent));
				}

				if (empty($jobTitle))
				{
					$jobTitle = "Owner";
				}							
				$data[trim("CONTACT INFO ".$contacts++)] = "$name ($jobTitle)";
				if ($contacts > 20) break;
			}

			foreach ($x->query("//span[@id='lblContact']") as $node)
			{
				$data[trim("CONTACT INFO ".$contacts++)] = $node->textContent;;
				if ($contacts > 20) break;

			}
			$data['SOURCE_URL'] = $url;

			$urlParts = explode("/", parse_url($url,PHP_URL_PATH));
			$category = $urlParts[3];
			$category = ucfirst(str_replace("-"," ",$category));
			$data['Category']=$category;

			unset($data['Raw Address']);
			log::info($data);
			
			//file_put_contents("d:/dev/demandforce/last.html",$html);
		
			db::store($type,$data,array('SOURCE_URL'),false);
			return true;
		}
	}

	static function reduceExtraSpaces($str)
	{
		return trim(preg_replace("/(\t|\n|\r| )+/"," ",$str));
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
$r = new bbb_auto();

$r->parseCommandLine();

