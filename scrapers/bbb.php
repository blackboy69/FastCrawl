<?php

include_once "config.inc";

class bbb extends baseScrape
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
		//$this->proxy = "198.52.128.247:80";
		//self::getInstance()->switchProxy("");

	//		$this->proxy = "localhost:8888";

		$this->timeout = 10;
		$this->debug=false;	/*
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/matched/salon/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/matched/Day+Spas/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/matched/Nail+Salons/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/salon/tanning-salons/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/salon/beauty-salons/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/beauty+school/beauty-school/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/barber/barber-schools/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/barber/barbers/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/massage/massage-therapists/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/medical+spa/hair-styling-and-services/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/medical+spa/health-resorts/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/name/Plastic+surgery/%ZIP%");


		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/auto/auto-body-repair-and-painting/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/auto/auto-lube-and-oil-mobile/%ZIP%");
		
	
		
		db::query("

		UPDATE load_queue
		 
		 SET processing = 17

		 WHERE
			 url IN (SELECT url FROM raw_data WHERE type ='$type')
			 AND type ='$type'
		");


		db::query("DELETE FROM RAW_DATA WHERE (html like '%we had to limit your access%' OR LENGTH(html) < 25000) and type ='bbb'");
		db::query("		
		UPDATE load_queue
		 
		 SET processing = 0

		 WHERE			 

			 
			 url NOT IN (SELECT url FROM raw_data WHERE type ='bbb')
			 AND type ='bbb'
		");
	
			*/
		//$this->loadUrl("http://www.bbb.org/greater-san-francisco/business-reviews/auto-repair-and-service/autometrics-auto-repair-in-berkeley-ca-10210"); //%ZIP%

		$toSearch = array("Plumbers","electricians","Heating, Air Conditioning","Landscaping","Pest Control","Home Cleaning","Roofing","Painting");
		foreach($toSearch as $search)
		{
			$search = urlencode($search);
			$this->loadUrlsByCity("http://goldengate.bbb.org/Find-Business-Reviews/matched/$search/%CITY%-%STATE%/",'',250);
		}
	}
	
   // basic load call back to populate the db.
   public static function loadCallBack($url,$html,$type)
   {

		$thiz =self::getInstance();
	   if (strpos($html,"we had to limit your access to this website") || strlen($html) < 15000 )
	   {		 			
			$html=null;
			if (strlen($html) > 0)
				log::info("Sorry, we had to limit your access to this website. ".strlen($html) );
			else
			{
				log::info("Zero size result. ".strlen($html) );
			}
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
		if (strpos($url, "Find-Business-Reviews"))
		{
			self::parseListings($url,$html);
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
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		

		$urls = array();

		foreach ($x->query("//td[@class='biz-info']//h3//a") as $node)
		{
			$href = $node->getAttribute("href");
			if (!strpos($href, "/file-a-complaint") &&
				!strpos($href, "businessreport.aspx?companyid="))
			{
				$urls[$href] = $href;
			}
		}
		if (sizeof($urls)>0)
		{
			self::getInstance()->loadUrlsByArray($urls);	
			return true;
		}
		return false;

	}

	static function parseDetails($url,$html)
	{
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$html = str_replace("<br />","\n",$html);


		$html = str_replace("&nbsp;"," ",$html);
		$html = str_replace("\t"," ",$html);

		$type = get_class();		

		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		
		$data = array();

		
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
			foreach ($x->query("//span[@class='business-address']") as $node)
			{				
				$data = array_merge($data,$ap->parse( $node->c14n() ));
			}

			foreach ($x->query("//span[@class='business-link']//a") as $node)
			{	
				$data['Url'] = $node->getAttribute("href");
			}
			


			foreach ($x->query("//div[@id='accedited-rating']//img") as $node)
			{	
				$title = $node->getAttribute("title");
				if (empty($title))
					$data['BBB Rating'] =  $node->getAttribute("alt");
				else
					$data['BBB Rating'] =  preg_replace("/^BBB.. /","", $title);
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
			
			$urlParts = explode("/", parse_url($url,PHP_URL_PATH));
			$category = $urlParts[3];
			$category = ucfirst(str_replace("-"," ",$category));
			$data['Category']=$category;

			unset($data['Raw Address']);
			log::info($data);
			
			//file_put_contents("d:/dev/demandforce/last.html",$html);
		
			db::store($type,$data,array('Name','Address','Telephone','Zip'),false);
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
$r = new bbb();

$r->parseCommandLine();

