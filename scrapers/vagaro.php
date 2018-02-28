<?
include_once "config.inc";

class vagaro extends baseScrape
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
//		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='vagaro' ");
		

		//$this->noProxy=true;

		// CHEAT: Use fiddler to get GetRandomActiveBusiness
		// call it with 1000 max records and 1000 radius

		$json = file_Get_contents("vagaro.json");
		$json_data = json_decode($json,true);

		foreach($json_data['d']["Rows"] as $data)
		{
			$data['SOURCE_URL'] = "";
				log::info($data["BusinessName"]);
			db::store($type,$data,array('BUSINESSID'));
		}



/*
		$this->threads=1; // DO NOT INCREASE!!!
		//$this->useCookies = true;
		//$this->timeout = 5;
		// http://www.vagaro.com/find-a-veterinarian-animal-hospital/california/santa-cruz/?radius=50
		
      $result = mysql_query("SELECT CITY, name as STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc LIMIT 25");      
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode(strtoupper($r[0]));
			$state = urlencode(ucfirst($r[1]));
			
			$url = "http://www.vagaro.com/Users/Featured/FeaturedBusiness.aspx?proximity=/$state/$city";
			log::info($url);
			$html = $this->Get($url);
			$this->parse($url,$html);

      }
		*/
	}
	
	static function parse($url,$html)
	{
		$thiz =self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$urls = array();

		$data = array();		
		
		$thiz->setCookies(array("ProximityCityState",$query['proximity']));
		$json = $thiz->POST("http://www.vagaro.com/WebServices/GeneralWebService.asmx/GetRandomActiveBusiness ", '{"startIndex" : 0, "maxRecords" : 10000, "sType" : "featured", "sFacilities" :"", "sBusinessTypes" : "", "sKidsCondition" : "", "iUserID" : 0, "sBusinessIds" : "[object Object]", "SortType" : 5, "blnPromotion" : "false", "sCurrentRating" : 0, "sSessionIDOfUser" : "", "SRandomGUID" : "", "sSearchRadious" : "500"}');

		$d = json_decode($json,true);
		log::info($d);
		return;
		foreach($d["Rows"] as $data)
		{
			$data['SOURCE_URL'] = $url;				
			log::info($data);
			db::store($type,$data,array('BUSINESSID'));
		}


	}



}

//db::query("UPDATE  raw_data set parsed=0 where type='vagaro' and parsed=1 ");
/*db::query("DROP TABLE vagaro ");
*/
$r = new vagaro();
$r->parseCommandLine();
