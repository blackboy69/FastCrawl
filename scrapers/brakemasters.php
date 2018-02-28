<?
include_once "config.inc";

class brakemasters extends baseScrape
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
//		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='brakemasters' ");
		

		//$this->noProxy=true;
		
		//db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='brakemasters' and url not like 'http://www.brakemasters.com/find-a-veterinarian-animal%' ");
		//db::query("drop table brakemasters");
		

		//$this->useCookies = true;
		//$this->timeout = 5;
		// http://www.brakemasters.com/find-a-veterinarian-animal-hospital/california/santa-cruz/?radius=50exit;

		
      //$result = mysql_query("SELECT CITY, name as STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc LIMIT 1000");  
      $result = mysql_query("SELECT distinct zip,state FROM geo.locations order by pop desc LIMIT 2000") ;
db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='brakemasters' ");
      while ($r = mysql_fetch_row($result))
      {
	//		$city = str_replace(" ","-", strtolower($r[0]));
//			$state = str_replace(" ","-", strtolower($r[1]));
			$zip = str_replace(" ","-", strtolower($r[0]));

			$url = "http://www.brakemasters.com/store-locator.php?$zip";
			$webRequests[] = new WebRequest($url,$type,"POST","searchZip=$zip&searchDistance=25");
        
      }
		//$this->loadWebRequests($webRequests);


		$this->loadurl("http://www.brakemasters.com/?sm-xml-search=1");
	}
	
	public static function parse($url,$html)
	{
		$type = get_class();		

		$json_data = json_decode($html,true);

		foreach($json_data as $data)
		{
			$data = db::normalize($data);
			$data['XID'] = $data['ID'];
			unset ($data['ID']);

			log::info($data);					

			db::store($type,$data,array('XID'));	
		}
	}

}

//db::query("UPDATE  raw_data set parsed=0 where type='brakemasters' and parsed=1 ");
/*db::query("DROP TABLE brakemasters ");
*/
$r = new brakemasters();
$r->parseCommandLine();
	