<?
include_once "config.inc";

class greasemonkey extends baseScrape
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
db::query("DROP TABLE $type");

		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		
*/
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
//		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='greasemonkey' ");
		

		//$this->noProxy=true;
		
		//db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='greasemonkey' and url not like 'http://www.greasemonkey.com/find-a-veterinarian-animal%' ");
		//db::query("drop table greasemonkey");
		

		//$this->useCookies = true;
		//$this->timeout = 5;
		// http://www.greasemonkey.com/find-a-veterinarian-animal-hospital/california/santa-cruz/?radius=50exit;
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
db::query("DROP TABLE $type");
		
      //$result = mysql_query("SELECT CITY, name as STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc LIMIT 1000");  
      $result = mysql_query("SELECT distinct zip,state FROM geo.locations order by pop desc LIMIT 2000") ;
db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='greasemonkey' ");
      while ($r = mysql_fetch_row($result))
      {
	//		$city = str_replace(" ","-", strtolower($r[0]));
//			$state = str_replace(" ","-", strtolower($r[1]));
			$zip = str_replace(" ","-", strtolower($r[0]));

			$url = "http://www.greasemonkey.com/store-locator.php?$zip";
			$webRequests[] = new WebRequest($url,$type,"POST","searchZip=$zip&searchDistance=25");
        
      }
		//$this->loadWebRequests($webRequests);


		for($pagenum=0;$pagenum<=20;$pagenum++)
		{
			$this->loadUrl("http://www.greasemonkeycorp.com/store-locator?state=IL&city=Chicago&dist=50000&pagenum=$pagenum");
		}
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

		$data = array();		
		
		$x = new Xpath($html);
		
		foreach ($x->query("//td[@colspan='2']") as $listing)
		{
			$x2 =  new Xpath($listing);


			foreach ($x2->query("//b") as $node)
			{
				$data['Name'] = $node->textContent;
			}
			
	
			$data = array_merge($data, $ap->parse($listing->textContent));
			$data = array_merge($data, $pp->parse($listing->textContent));


			foreach ($x2->query("//a[contains(text(),'Grease Monkey of ')]") as $node)
			{
				$data['WEBSITE']= $node->getAttribute("href");;
			}

			$data['SOURCE_URL'] = $url;

			log::info($data);								
			db::store($type,$data,array('NAME','CITY','ADDRESS'),false);
		}
	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='greasemonkey' and parsed=1 ");
/*db::query("DROP TABLE greasemonkey ");
*/
$r = new greasemonkey();
$r->parseCommandLine();
	