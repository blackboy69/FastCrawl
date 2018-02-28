<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class optometrists_sk_ca extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
	//	$this->noProxy=false;
//		$this->proxy = "localhost:9666";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		
		//
	/*			
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");		*/
		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='optometrists_sk_ca' and parsed = 1 ");
	
	//	
	

//		db::query("UPDATE raw_data set parsed = 0 where type='optometrists_sk_ca' and parsed = 1 ");
//		db::query("	DROP TABLE optometrists_sk_ca"); 

		// load all cities 
		$cities  = array ('Assiniboia', 'Balcarres', 'Biggar', 'Canora', 'Carlyle', 'Carnduff', 'Emerald Park', 'Esterhazy', 'Estevan', 'Fort Qu\'Appelle', 'Grenfell', 'Hudson Bay', 'Humboldt', 'Indian Head', 'Kindersley', 'Kipling', 'La Ronge', 'Lloydminster', 'Lumsden', 'Maple Creek', 'Martensville', 'Meadow Lake', 'Melfort', 'Melville', 'Moose Jaw', 'Moosomin', 'Nipawin', 'North Battleford', 'North Battleford  S9A 2Z3', 'North Battleford S9A 4A9', 'Outlook', 'Prince Albert', 'Radville', 'Regina', 'Rosetown', 'Rosthern', 'Saskatoon', 'Shaunavon', 'Swift Current', 'Tisdale', 'Unity', 'Wadena', 'Wakaw', 'Warman', 'Watrous', 'Weyburn', 'Whitewood', 'Wynyard', 'Yorkton');


      foreach ($cities as $city)
      {
			$city = urlencode($city);
			$urls[] = "http://optometrists.sk.ca/Search%20Results?&docName=Last%20Name&docCity=$city";
      }
      return  $this->loadUrlsByArray($urls);   
		

	//	$this->loadUrl("http://www.optometrists.bc.ca/code/navigate.aspx?Id=88&action=1&cmbCity=&txtLastName=");
	}
	

	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		$ep = new Email_Parser();

		parse_str(parse_url($url,PHP_URL_QUERY),$qs);
		
		log::info("In Listing");
		$xListing = new XPath($html);	
		$data = array();
		foreach ($xListing->query("//div[contains(@class,'mi-userList')]/div") as $nodeListing)
		{			
			$x = new XPath($nodeListing);	

			$class = self::cleanup(strtolower(trim($nodeListing->getAttribute("class"))));


			if (preg_match("/^un/i", $class ) )
			{			
				$data = array();				
				$data['DOCTOR_NAME'] = trim($nodeListing->textContent);
				$data['STATE'] = 'SK';
			}
			else if (isset($data['DOCTOR_NAME'] ))
			{
				if (preg_match("/^un/i", $class ) )
					$data['DOCTOR_NAME'] += " ".trim($nodeListing->textContent);

				else if (preg_match("/^uo/i", $class ) )
					$data['COMPANY_NAME'] = trim($nodeListing->textContent);

				else if (preg_match("/^ua/i", $class ) )
					$data['ADDRESS'] = trim($nodeListing->textContent);
				
				else if (preg_match("/^uc/i", $class ) )
					$data['CITY'] = trim($nodeListing->textContent);

				else if (preg_match("/^uw/i", $class ) )
					$data['PHONE'] = trim( str_ireplace("Phone:","",$nodeListing->textContent));

				else if (preg_match("/^up/i", $class ) )
					$data['ZIP'] = trim( $nodeListing->textContent);
				
				else if (preg_match("/^uf/i", $class ) )
				{
					// fax is last field.
					$data['FAX'] = trim( str_ireplace("FAX:","",$nodeListing->textContent));

					$data['SOURCE_URL'] = $url;
					log::info($data);		

					if(!empty($data['PHONE']))
						db::store($type,$data,array('PHONE'));	
//					else
						//log::error("NOT SAVED");		

					$dn = $data['DOCTOR_NAME'];
					$data = array();
					$data['DOCTOR_NAME'] = $dn;
				}
			}
			else
			{
				log::error($nodeListing->c14n());
			}
		}
					
		if (! empty($data['NAME'] ) &&!empty($data['PHONE']))
		{

		}
	}
}

$r= new optometrists_sk_ca();
$r->parseCommandLine();

