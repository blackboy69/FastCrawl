<?
include_once "config.inc";

class autoplus extends baseScrape
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
//		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='autoplus' ");
		

		//$this->noProxy=true;
		
		//db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='autoplus' and url not like 'http://www.autoplus.com/find-a-veterinarian-animal%' ");
		//db::query("drop table autoplus");
		

		//$this->useCookies = true;
		//$this->timeout = 5;
		// http://www.autoplus.com/find-a-veterinarian-animal-hospital/california/santa-cruz/?radius=50exit;

		
      //$result = mysql_query("SELECT CITY, name as STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc LIMIT 1000");  

		$this->loadPostUrl("http://www.autoplus.biz/locator.php","address=30144&search_type=AS%2B%7CSAX&radius=5001&start=1&lat=34.0235914&lng=-84.59557319999999");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
     //	db::query("UPDATE  load_queue set processing=0 where type='$type' ");
		db::query("DROP TABLE $type");

	}
	
	static function parse($url,$html)
	{	
		
		$type = get_class();	
		$t=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$urls = array();

		

		$scriptListings = array();
		$x = new Xpath($html);
      foreach ($x->query('//script') as $listing)
		{
         foreach (explode(";",$listing->textContent) as $scriptLine)
         {
            if (preg_match("/tmp.description =/",$scriptLine)) 
               continue;
            if (preg_match("/var tmp =/",$scriptLine)) 
               continue;
            if (preg_match("/^ tmp\./",$scriptLine)) 
               continue;
            if (preg_match("/addMarker/",$scriptLine)) 
               continue;
            else
            {

              $line = str_replace("\\'", "'", $scriptLine);
              $line = str_replace("'>","",$line);
              // $line = preg_replace("/';\$/","",$line);

               $scriptListings[] = $line;
            }
         }
      }
		
      

		foreach ($scriptListings as $listing)
		{
			$parts = preg_split("/<br>/",$listing );


         if (sizeof($parts > 4))
         {			
            $data = array();		
            $data['Name'] = self::cleanup(strip_tags($parts[0]));
            if (empty($data['Name'])) continue;


            $data = array_merge($data, $ap->parse($parts[1] .", " . $parts[2]));
            $data['WEBSITE'] = self::cleanup(strip_tags($parts[5]));
            $data['PHONE'] = self::cleanup(strip_tags($parts[3]));

            $data['SOURCE_URL'] = $url;
         
            log::info($data);								
            db::store($type,$data,array('NAME','CITY','ADDRESS'),false);
         }
		}
	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='autoplus' and parsed=1 ");
/*db::query("DROP TABLE autoplus ");
*/
$r = new autoplus();
$r->parseCommandLine();
	