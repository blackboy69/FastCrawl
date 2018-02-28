<?php

include_once "config.inc";

class goldengate_bbb extends baseScrape
{
   public static $_this=null;
	public static $numCalls = 0;


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

  $scrape =   new goldengate_bbb();
  $i = 0;
  // so we use a forever loop to check the raw_data  (the results of the load queue) to see if there is any more data left to parse
  for (;;)
  {
		$type = get_class($scrape);

		try
		{	
			// lock the tables so we can saturate the cpu with multiple processes
			// if we had innodb we would use row level locking by using BEGIN TRAN; SELECT ... LOCK IN SHARED MODE; COMMIT TRAN;
			/*if (! mysql_query("LOCK TABLES raw_data WRITE "))
			{
				log::error(mysql_error);
				continue;
			}*/

			// now we just iterate raw_data, pull out what we want and stick in the correct table.
			// use for update to keep this thread safe.
			$row = db::query("SELECT url,html,parsed FROM raw_data WHERE type = '$type' and parsed = 0 LIMIT 1");

			if (empty($row))
			{
				log::info("Waiting for data");
				sleep(2);
				continue;
			}
			$url = $row['url'];
			db::query("UPDATE raw_data SET parsed = 1 WHERE type='$type' AND url = '".db::quote($url)."'");			
			//mysql_query("UNLOCK TABLES");

			$html = str_replace('&nbsp;'," <br> ", $row['html']);


	
			log::info("Parsing: $url");		
			goldengate_bbb::parse($url, $html);

			//db::query("COMMIT");


		}
		catch (exception $e) 
		{
			//mysql_query("ROLLBACK");		
			db::query("UPDATE raw_data SET parsed = 0 WHERE type='$type' AND url = '".db::quote($url)."'");
			//mysql_query("UNLOCK TABLES");
			throw $e;
		}

  }

