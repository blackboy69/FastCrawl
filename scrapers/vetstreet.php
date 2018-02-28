<?
include_once "config.inc";

class vetstreet extends baseScrape
{
    public static $_this=null;
	
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
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		
*/
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
//		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='vetstreet' ");
		

		//$this->noProxy=true;
		
		//db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='vetstreet' and url not like 'http://www.vetstreet.com/find-a-veterinarian-animal%' ");
		//db::query("drop table vetstreet");
		
		$this->threads=1;
		//$this->useCookies = true;
		//$this->timeout = 5;
		// http://www.vetstreet.com/find-a-veterinarian-animal-hospital/california/santa-cruz/?radius=50
		
      $result = mysql_query("SELECT CITY, name as STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc LIMIT 6000");      
      while ($r = mysql_fetch_row($result))
      {
			$city = str_replace(" ","-", strtolower($r[0]));
			$state = str_replace(" ","-", strtolower($r[1]));

			$url = "http://www.vetstreet.com/find-a-veterinarian-animal-hospital/$state/$city/?radius=25";
			$webRequests[] = new WebRequest($url,$type);
        
      }
		$this->loadWebRequests($webRequests);
	}
	
	static function parse($url,$html)
	{
		$t=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$urls = array();

		$data = array();		
		

		$x = new Xpath($html);
		
		foreach($x->query("//div[@id='pagination']//a") as $node)
		{
			$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
		}
		
		foreach($x->query("//span[@class='practice_name']//a") as $node)
		{
			$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
		}
		
		if (! empty($urls))
		{
			$t->loadUrlsByArray($urls);
			return;
		}

		foreach($x->query("//h1[@itemprop='name']") as $node)
		{
			$data['COMPANY'] = self::cleanup($node->textContent);
		}

		foreach($x->query("//address") as $node)
		{
			$data = array_merge($data,$ap->parse($node->textContent));
		}
		
		foreach($x->query("//*[contains(@id,'phone')]") as $node)
		{
				$data['PHONE'] =  $node->textContent;
		}	

		foreach($x->query("//div[@id='request-appointment']/a") as $node)
		{
				$data['PAID_LISTING'] =  $node->textContent;
		}	

		
		//email
		$data = array_merge($data,$ep->parse($html));
		
		unset ($data['RAW_ADDRESS']);
	
		foreach($x->query("//p[@id='website']//a") as $node)
		{
			$data['WEB_SITE'] = $node->getAttribute("href");
		}

		$categories = array();
		foreach($x->query("//div[@id='practice-services']//li") as $node)
		{
			$categories[] = $node->textContent;
		}
		if (!empty($categories))
			$data['CATEGORIES'] = join(", ",$categories);

		if (isset($data['COMPANY']))
		{
			$data['SOURCE_URL'] = $url;				
			//log::info($data);
			echo ".";
			db::store($type,$data,array('SOURCE_URL'));
		}


	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='vetstreet' and parsed=1 ");
/*db::query("DROP TABLE vetstreet ");
*/
$r = new vetstreet();
$r->parseCommandLine();
	