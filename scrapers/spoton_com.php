<?
include_once "config.inc";

class spoton_com extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {

		$type = get_class();	
		//db::query("drop table $type ");
//		db::query("delete from raw_data where type='$type' ");
	//	db::query("delete from load_queue where type='$type'"); 
		$this->loadUrlsByCity("http://spoton.com/findspoton/maps/merchants/?geo=%LAT%%2C%LON%&radius=0.003905875812825907");
	}
	
	static function parse($url,$html)
	{
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$json = json_decode($html,true);

		foreach ($json as $data)
		{
							
			$data['XID'] = $data['id'];
			$data = db::normalize($data);
			unset($data["ID"]);
			unset($data["id"]);
			unset($data["0"]);
			unset($data["1"]);
			$data['SOURCE_URL'] = $url;				

			log::info($data);
			db::store($type,$data,array('NAME','PHONE'));
			unset($data);
		}

	}
}

//db::query("DELETE FROM raw_data where type='spoton_com'  ");
//db::query("DELETE FROM load_queue where type='spoton_com'");

$r = new spoton_com();
$r->parseCommandLine();
