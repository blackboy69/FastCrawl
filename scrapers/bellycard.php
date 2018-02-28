<?
include_once "config.inc";

class bellycard extends baseScrape
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



		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");

		
*/
		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		//db::query("DROP TABLE $type");	
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
//..		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='bellycard' ");
//		db::query("DROP TABLE $type");	

		//$this->noProxy=true;

//
		$this->loadUrlsByCity("https://api.bellycard.com/api/businesses/search?lon=%LON%&lat=%LAT%");
	}
	
	static function parse($url,$html)
	{
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$json = json_decode($html,true);
		
		foreach ($json as $data)
		{
		
			unset($data['chain']['business_ids']);
			unset($data['market']);
			unset($data['hours']);
			unset($data['featured_reward']);

				
			$data['XID'] = $data['id'];
			$data = db::normalize($data);
			unset($data["ID"]);


			$data['SOURCE_URL'] = $url;				
echo ".";
static $i=0;
if($i++%100==0)echo "\n" ;

			db::store($type,$data,array('XID'));
		}

	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='bellycard' and parsed=1 ");
/*db::query("DROP TABLE bellycard ");
*/
$r = new bellycard();
$r->parseCommandLine();
