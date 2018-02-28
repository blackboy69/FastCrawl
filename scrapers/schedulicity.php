<?
include_once "config.inc";

class schedulicity extends baseScrape
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
//..		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='schedulicity' ");
//		db::query("DROP TABLE $type");	

		//$this->noProxy=true;

//
		$this->loadUrlsByCity("https://www.schedulicity.com/api/v2.1/Business/Search?where=%CITY%,+%STATE%&query=&page=1&industryTypeId=&pageSize=10");
	}
	
	static function parse($url,$html)
	{
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$json = json_decode($html,true);

		if ($json['isMore'])
		{
			$where = urlencode($query['where']);
			$page = (1 + $query['page']);
			$toLoad =  "https://www.schedulicity.com/api/v2.1/Business/Search?where=$where&query=&page=$page&industryTypeId=&pageSize=10";
			log::info("Next page");
			$thiz->loadUrl($toLoad);
		}

		foreach ($json['SearchResults'] as $data)
		{
			unset($data["RAW_ADDRESS"]);
			$data['SOURCE_URL'] = $url;				
			echo ".";

			db::store($type,$data,array('BusinessID'));
		}

	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='schedulicity' and parsed=1 ");
/*db::query("DROP TABLE schedulicity ");
*/
$r = new schedulicity();
$r->parseCommandLine();
