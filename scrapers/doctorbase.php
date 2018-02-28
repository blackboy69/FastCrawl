<?
include_once "config.inc";

class doctorbase extends baseScrape
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
//		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='doctorbase' ");
		

		//$this->noProxy=true;

		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	

		//$this->useCookies = true;
		//$this->timeout = 5;
		// http://www.doctorbase.com/find-a-veterinarian-animal-hospital/california/santa-cruz/?radius=50
		
      $result = mysql_query("SELECT CITY, name as STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc LIMIT 500");      
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode(strtoupper($r[0]));
			$state = urlencode(ucfirst($r[1]));

			$urls[] = "http://doctorbase.com/api/dr/search_rest?location=$city,+$state&specialty_id=&procedure=&insurance_company=&insurance_type=&sort_by=&radius=20&dr_name=&page=&render_results=1&specialty_facets=1&show_specialty_counts=1";
      }

		$this->loadUrlsByArray($urls);

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


		$d = json_decode($html,true);


		foreach($d['drs'] as $data)
		{
			$data['SOURCE_URL'] = $url;	
			unset($data['id']);
			log::info($data);
			db::store($type,$data,array('BLOG_URL'));
		}


	}



}

//db::query("UPDATE  raw_data set parsed=0 where type='doctorbase' and parsed=1 ");
/*db::query("DROP TABLE doctorbase ");
*/
$r = new doctorbase();
$r->parseCommandLine();
