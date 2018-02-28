<?
include_once "config.inc";

class carx extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' ");
//		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed =1");
//		db::query("DELETE FROM raw_data where type='$type' ");
	//	db::query("DROP TABLE $type");
		#db::query("DELETE FROM $type");
			
		$this->threads=4;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
		// there aren't any more than this, really this is all there are!
		//$this->loadUrl("http://www.carx.com/googleLocations.asp?address=60173");
		$this->loadUrlsByLocation("http://net.carx.com/public/ajax/lookupLocationsRadius.asp?radius=600&lat=%LAT%&lng=%LON%");
		
   }
	

	static function parse($url,$html)
	{
		
		$oh = new Operating_Hours_Parser();

		$json = json_decode($html);

		$hide['distance'] = true;
		$hide['hours'] = true;
		static $count=0;
//	print_r($json);
		foreach ($json as $listing)
		{
//			print_R($listing);
			$data=array();	
			foreach($listing as $k => $v)
			{
				// we don't need distance
				if (!isset($hide[$k]))
				{
					$data[$k] = $v;
				}
			}

			$data = array_merge($data,$oh->parse($listing->hours1));

			$data['xid'] = $data['id']			;
			unset($data['id']);
			unset($data['dealerid']);

			$data['SOURCE_URL'] = $url;
			$data['WEBSITE'] = "http://www.carx.com".	$data['web'];
			unset($data['web']);

			log::info($data['locname']);					
			db::store('carx',$data,array('xid'),false);		
			
			$count++;
		}

		log::info("parsed $count stores");
	}


	static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/","",$str);
	}
	
}
$r = new carx();
$r->parseCommandLine();
