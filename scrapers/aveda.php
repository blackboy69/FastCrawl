<?
include_once "config.inc";

class aveda extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
	/*	

		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");

			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		db::query("DROP TABLE $type");
*/
		 $result = mysql_query("
				SELECT 
					geo.us_cities.city as CITY, 
					geo.us_cities.state AS STATE,
					MAX( geo.locations.zip ) as ZIP ,  
					max(lat) as LAT ,
					max(lon) as LON
				FROM  geo.us_cities 
				INNER JOIN geo.locations ON (geo.us_cities.city = geo.locations.city AND geo.US_CITIES.STATE  = GEO.locations.state)
				GROUP BY geo.us_cities.city, geo.us_cities.state LIMIT 500
			");

      while ($r = mysql_fetch_row($result))
      {
			$city=$r[0];
			$state = $r[1];
			$zip = sprintf("%05d", $r[2]);
			$lat = $r[3];
			$lon = $r[4];

			$url = "http://www.aveda.com/rpc/jsonrpc.tmpl?dbgmethod=locator.doorsandevents&scrape_progress=".urlencode("$state.$city.$lat.$lon");

			$toPost = 'JSONRPC=[{"method":"locator.doorsandevents","id":7,"params":[{"fields":"DOOR_ID, SALON_ID, ACTUAL_DOORNAME, ACTUAL_ADDRESS, ACTUAL_ADDRESS2, ACTUAL_CITY, STATE, ZIP, DOORNAME, ADDRESS, ADDRESS2, CITY, STATE_OR_PROVINCE, ZIP_OR_POSTAL, COUNTRY, PHONE1, CLASSIFICATION, IS_SALON, IS_LIFESTYLE_SALON, IS_INSTITUTE, IS_FAMILY_SALON, IS_CONCEPT_SALON, IS_STORE, HAS_EXCLUSIVE_HAIR_COLOR, HAS_PURE_PRIVILEGE, HAS_PERSONAL_BLENDS, HAS_GIFT_CARDS, HAS_PAGE, HAS_SPA_SERVICES, IS_GREEN_SALON, HAS_RITUALS, DO_NOT_REFER, HAS_EVENTS, LONGITUDE, LATITUDE, LOCATION, WEBURL, EMAILADDRESS","radius":"100","btn":"dom","uom":"mile","latitude":'.$lat.',"longitude":'.$lon.',"doorname":"","country":"USA","primary_filter":"filter_salon_spa_store","filter_HC":0,"filter_PP":0,"filter_SS":0,"filter_EM":0,"filter_SR":0}]}]';

			$webRequests[] = new WebRequest($url,$type,"POST",$toPost);
      }
      log::info ("Loaded ".sizeof($webRequests)." urls by zip code for $type");
      return  $this->loadWebRequests($webRequests);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$data = array();
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		
		$d = json_decode($html,true);

log::info("$url\n");
		foreach($d[0]['result']['value']['sorted_results'] as $data)
		{
			unset($data["LOCATION"]);
			echo ">";

			db::store($type,$data,array('DOOR_ID'));	
		}



	}
}
$r= new aveda();
$r->parseCommandLine();

