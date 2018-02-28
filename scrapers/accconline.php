<?
include_once "config.inc";

class accconline extends baseScrape
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
	*/
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		db::query("DROP TABLE $type");

		foreach(db::query("SELECT zip FROM geo.locations order by pop desc limit 500 ",true) as $row)
      {
			$url = "http://www.accconline.com/consumer/dealer-location-results.php#{$row['zip']}";
			$toPost = "PostalCode={$row['zip']}&radius=100&city=&state=&x=32&y=21";
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
		


		/* 
			  	 var point = new GLatLng(39.437317,-123.805603);
      var marker = createMarker(point,"COAST TIRE - TIRE PROS", "440 S MAIN ST<br>FORT BRAGG, CA 95437", "96.13", "(707)964-6682","<strong>COAST TIRE - TIRE PROS</strong><br>440 S MAIN ST<br>FORT BRAGG, CA 95437<p>Phone: (707)964-6682</p>Get Directions To Here From:<form action=\"http://maps.google.com/maps\" method=\"get\" target=\"_blank\"><input type=\"text\" SIZE=30 MAXLENGTH=40 name=\"saddr\" id=\"saddr\" value=\"\" /><br><INPUT value=\"Get Directions\" TYPE=\"SUBMIT\"><input type=\"hidden\" name=\"daddr\" value=\"440 S MAIN ST FORT BRAGG, CA 95437\"/>", "")
      map.addOverlay(marker);
		*/

		preg_match_all('/var marker = createMarker.point,".+?",".*?"./',$html, $matches,PREG_SET_ORDER);
		
		foreach($matches as $index => $m)
		{
			$match = str_replace("<br>",",", $m[0]);
			$splitMatch = explode(",", $match);
			
			$data = array();
			$data["NAME"] = $splitMatch[1];
			$data = array_merge($data,$ap->parse($match));
			$data = array_merge($data,$pp->parse($match));

			$data = db::normalize($data);
			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);					
				db::store($type,$data,array('NAME','PHONE'));	
			}
			else
			{
				log::error("Not Found");
				log::error($url);
			}
		}
	}
}
$r= new accconline();
$r->parseCommandLine();

