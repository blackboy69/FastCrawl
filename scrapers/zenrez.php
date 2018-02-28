<?
include_once "config.inc";

class zenrez extends baseScrape
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




*/
		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		//db::query("DROP TABLE $type");	
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
//..		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='zenrez' ");
//		

		//$this->noProxy=true;

//
/*
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");

		*/
		$this->loadUrl("https://www.zenrez.com/api/v1.1/regions/54f4c2b7fa9919bb4327c196/mapdata?_city=1&_classTypes=1&_neighborhood=1");
		$this->loadUrl("https://www.zenrez.com/api/v1.1/regions/5771325fbfe31c0e00ec38ae/mapdata?_city=1&_classTypes=1&_neighborhood=1");
		$this->loadUrl("https://www.zenrez.com/api/v1.1/regions/5775cf9a457fae0e00663044/mapdata?_city=1&_classTypes=1&_neighborhood=1");
		$this->loadUrl("https://www.zenrez.com/api/v1.1/regions/54fe0021438361a049725595/mapdata?_city=1&_classTypes=1&_neighborhood=1");
		$this->loadUrl("https://www.zenrez.com/api/v1.1/regions/577c30a507c6820e009476e7/mapdata?_city=1&_classTypes=1&_neighborhood=1");
		$this->loadUrl("https://www.zenrez.com/api/v1.1/regions/552c35e38e9cd40e00f02c8a/mapdata?_city=1&_classTypes=1&_neighborhood=1");
		$this->loadUrl("https://www.zenrez.com/api/v1.1/regions/57817c86f7b18e0e005210f2/mapdata?_city=1&_classTypes=1&_neighborhood=1");
		
	}
	
	static function parse($url,$html)
	{
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$html = preg_replace('/[^(\x20-\x7F)\x0A\x0D]*/','', $html);
		
		file_put_contents("json",$html);
		$allData = json_decode($html,true);
	
		$count=0;
		log::info($url);
		foreach($allData['studios'] as $mainData)
		{
			$count++;
			if (isset($mainData['mindbody']['siteId']) && $mainData['mindbody']['siteId'] > 0)
				$mainData['MINDBODY_SITEID']=$mainData['mindbody']['siteId'];
			
			$data = array();
			$classTypes=array();
			
			foreach ($mainData as $k=>$v)
			{
				if (is_array($v))
					continue;
				
				$data[$k]=$v;
			}
			foreach($mainData['_classTypes'] as $ct)
			{
				$classTypes[] = $ct['name'];
			}			
			
			$data['CATEGORIES'] = join(', ', $classTypes);
		
			
			$data = db::normalize($data);
			$data['XID'] = $data['ID'];
			
			unset($data['CITY']);
			unset($data['REGION']);
			unset($data['ID']);
			unset($data['BUSINESS']);
			
		//	log::info($data);		
			
			db::store($type,$data, array("XID"));	

		}

		log::info("$url: \nFound $count Length: ".strlen($html));
		
	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='zenrez' and parsed=1 ");
/*db::query("DROP TABLE zenrez ");
*/
$r = new zenrez();
$r->parseCommandLine();
