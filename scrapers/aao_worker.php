<?php

include_once "config.inc";

class aao extends baseScrape
{
   public static $_this=null;
	public static $numCalls = 0;


	
	static function parse($url,$html)
	{
		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query);
		
		if (isset($query['fuseaction']))
		{
			switch ($query['fuseaction'])
			{
				case "search":
					self::parseListings($url,$html);
					break;
				case "profile":
					self::parseDetails($url,$html);
					break;
				default:
					log::info("Unknown Action: ".$query['fuseaction']);
			}
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


		foreach ($x->query("//a") as $node)
		{
			$href = self::relative2absolute($url, $node->getAttribute("href"));

			parse_str(parse_url($href,PHP_URL_QUERY),$query);
			$fa = isset($query['fuseaction'])? $query['fuseaction'] : null ;

			if  ($fa ==  'profile' || $fa ==  'search')				
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
		$x = new HtmlParser($html);
		$data = array();

		foreach ($x->query("//table[@width='470']/tr[2]") as $node)
		{	
			$data['Name'] = self::reduceExtraSpaces($node->textContent);
		}
		if (isset($data['Name']))
		{
			
			foreach ($x->query("//table[@width='470']/tr") as $node)
			{	
				$x2 = new HtmlParser($node->c14n());
				$key = $value = "";
				$i=0;
				foreach ($x2->query("//td") as $node2)
				{		
					$i++;
					$td = $node2->textContent;
					if ($i == 2)
					{
						$key= trim(str_replace(":","",$td));
					}
					if ($i == 3)
					{
						$value=$td;
					}
				}
				if(!empty($key))
				{
					$data[$key]=$value;
				}
			}
			
			$data = self::PhoneAddressParse($data, "Primary Office");
			$data = self::PhoneAddressParse($data, "Second Office");
			$data = self::PhoneAddressParse($data, "Third Office");
			
			db::store($type,$data,array('Name','Primary Office'),false);
			log::info($data);
		}
		else
		{
			log::info("No data for $url");
			//db::query("UPDATE load_queue SET processing = 0 WHERE url='$url' and type='$type'");	
		}


	}

	static function PhoneAddressParse($data, $type="Primary Office")
	{
		if (isset($data[$type]))
		{
			$ap = new Address_Parser();
			foreach($ap->parse($data[$type]) as $k=>$v)
			{
				$data["$type $k"] = $v;
			}
			if (preg_match("/Ph: ([0-9\)\(\- ]+)/",$data[$type],$matches))
			{
				$data["$type Telephone"]=$matches[1];
			}
			unset ($data["$type Raw Address"]);
		}
		return $data;

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

  $scrape =   new aao();
  $i = 0;
  // so we use a forever loop to check the raw_data  (the results of the load queue) to see if there is any more data left to parse
  for (;;)
  {
		$type = get_class($scrape);

		try
		{	
			//Db::begin();
			// now we just iterate raw_data, pull out what we want and stick in the correct table.
			// use for update to keep this thread safe.
			$row = db::query("SELECT url,html FROM raw_data WHERE type = '$type' and parsed = 0 LIMIT 1");
			

			if (empty($row))
			{
				log::info("Waiting for data");
				sleep(2);
				continue;
			}

			$url = $row['url'];
			$html = str_replace('&nbsp;'," <br> ", $row['html']);

			log::debug("Parsing: $url");		
			aao::parse($url, $html);
			db::query("UPDATE raw_data SET parsed = 1 WHERE type='$type' AND url = '".db::quote($url)."'");
				//		Db::commit();

		}
		catch (exception $e) 
		{
				//		Db::rollback();
			db::query("UPDATE raw_data SET parsed = 0 WHERE type='$type' AND url = '".db::quote($url)."'");
			throw $e;
		}

  }

