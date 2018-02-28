<?
include_once "config.inc";

class bellycard_com extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		
		$type = get_class();	
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

//db::query("UPDATE  raw_data set parsed=0 where type='bellycard_com' and parsed=1 ");
/*db::query("DROP TABLE bellycard_com ");
*/
$r = new bellycard_com();
$r->parseCommandLine();
