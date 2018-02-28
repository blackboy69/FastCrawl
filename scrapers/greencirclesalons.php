<?
include_once "config.inc";

class greencirclesalons extends baseScrape
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

		$this->loadUrl("http://www.greencirclesalons.ca/directory.aspx");
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
			  	 codeAddress('43.6768039','-79.3976538','Loki','<img src="http://www.greencirclesalons.ca/uploads/listings/45/thumbs/DSC_0002_0.JPG" align=left style="margin-right: 5px;"><b>Loki</b><br>236 Avenue Rd. 3rd floor<br>Toronto, ON M5R 2J4<br>Phone: (416) 961-3600 <br>Website: <a href="http://www.lokisalon.com" target=_blank>http://www.lokisalon.com</a>');
		*/

		preg_match_all("/codeAddress\('.+','.+','(.+)','(.+)'/",$html, $matches,PREG_SET_ORDER);

		
		foreach($matches as $index => $m)
		{
			$match = str_replace("<br>",",", $m[0]);
			$splitMatch = explode(",", $match);
			
			$data = array();
			$data["NAME"] = str_replace("'", "", $splitMatch[2]);
			$data = array_merge($data,$ap->parse($match));
			$data = array_merge($data,$pp->parse($match));


			$data = db::normalize($data);
			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;
				if (preg_match('/href="(.+?)"/',$match,$matches))
					$data['WEBSITE'] = $matches[1];

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
$r= new greencirclesalons();
$r->parseCommandLine();

