<?
include_once "config.inc";

class unit_test extends baseScrape
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
//..		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='unit_test' ");
//		db::query("DROP TABLE $type");	

		//$this->noProxy=true;

//
		$this->loadUrl("http://www.fivestars.com/api/v2/businesses/?near=29.86,-95.53");

	}
	
	static function parse($url,$html)
	{
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$json = json_decode($html,true);

		if ($json['meta']['next'] != null)
		{
			$toLoad =  $json['meta']['next'];
			log::info("Next page");
			$thiz->loadUrl($toLoad);
		}

		foreach ($json['data']['businesses'] as $data)
		{
			unset($data["RAW_ADDRESS"]);
			unset($data["ondeck"]);
			unset($data["rewards"]);

			$data['SOURCE_URL'] = $url;				
			log::info($data);
			db::store($type,$data,array('URL'));
		}

	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='unit_test' and parsed=1 ");
/*db::query("DROP TABLE unit_test ");
*/
$r = new unit_test();
$r->parseCommandLine();
