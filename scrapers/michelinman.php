<?
include_once "config.inc";

class michelinman extends baseScrape
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
//..		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='michelinman' ");
//		db::query("DROP TABLE $type");	

		//$this->noProxy=true;

//
//		$this->loadUrlsByCity("https://www.michelinman.com/api/v2.1/Business/Search?where=%CITY%,+%STATE%&query=&page=1&industryTypeId=&pageSize=10");
      $result = mysql_query("SELECT MAX( geo.locations.zip ) as zip , MAX( geo.us_cities.Pop ) as pop , max(lat) as lat ,max(lon) as lon, geo.us_cities.city as city, geo.us_cities.state
										FROM  geo.us_cities 
										INNER JOIN geo.locations ON geo.us_cities.city = geo.locations.city


										GROUP BY geo.us_cities.city, geo.us_cities.state
										");      

//		db::query("DROP TABLE $type");	
      while ($r = mysql_fetch_row($result) )
      {
			$zip = $r[0];
			$pop = $r[1];
			$lat = number_format($r[2],7);
			$lon = number_format($r[3],7);
			$city = urlencode($r[4]);
			$state = urlencode($r[5]);
			
			$urls[] = "http://www.michelinman.com/dealer-locator/dealer-locator.page?stringRadius=50&searchAddress=$zip&latitude=$lat&longitude=$lon&state=$state&city=$city&dl_postal_code=$zip";

			


		}
		$this->loadUrlsByArray($urls);
	}

	static function loadCallBack($url,$html,$arg3)
	{
		$x = new  XPath($html);	
		$type = get_class();		
		$thiz = self::getInstance();

		#Name
		foreach ($x->query("//form[@id='searchGSA']") as $node)
		{
			parse_str(parse_url($node->getAttribute("action"),PHP_URL_QUERY),$query); // address and zip				
		}
		$json = $thiz->get("http://www.michelinman.com/iwov-resources/jsp/dealerlocatorJSON.jsp?_={$query['componentID']}");		

		baseScrape::loadCallBack($url,$json,$arg3);
	}

	static function parse($url,$html)
	{
		log::info("In parse");
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		$ep = new Email_Parser();

		$data = array();
		
		$json = json_decode($html,true);
		foreach($json as $data)
		{
			$data['SOURCE_URL'] = $url;
			$data['XID'] =			$data['name'].$data['zip'];
			unset(			$data['id']);
			unset(			$data['dealerfilter']);
						unset(			$data['filters']);
			unset(			$data['hours']);
			unset(			$data['info']);
						unset(			$data['coor']);
			log::info( $data['XID'] );
			db::store($type,$data,array('XID'));
		}

	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='michelinman' and parsed=1 ");
/*db::query("DROP TABLE michelinman ");
*/
$r = new michelinman();
$r->parseCommandLine();
