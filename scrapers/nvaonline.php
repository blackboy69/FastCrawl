<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class nvaonline extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=8;
		$this->debug=false;
		
		//
	/*	
		
		
			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			

		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='nvaonline' and parsed = 1 ");

		db::query("	DROP TABLE nvaonline"); 
		db::query("UPDATE raw_data set parsed = 0 where type='nvaonline' and parsed = 1 ");
	*/

		foreach(db::query("SELECT state,max(zip) as zip from geo.locations  group by state",true) as $row)
		{
			$urls[] = "http://www.nvaonline.com/flashmap/fmMap/info/list.php?level=2&area=us_".strtolower($row['state'])."&zip=".$row['zip']."&distance=50000";
		}
		

		$this->loadUrlsByArray($urls);

		//db::query("UPDATE load_queue set processing = 0 where type='nvaonline' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='nvaonline' and parsed = 1 ");
		db::query("DROP TABLE $type");	
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();
		if (preg_match("/nvaonline.com\/flashmap\/fmMap\/info\/list.php/",$url))
		{
			parse_str(parse_url($url,PHP_URL_QUERY),$q); // address and zip
			log::info("Loading " . $q['area']);

			$urls = array();
			// load listings
			foreach ($x->query("//a[text()='Details']") as $node)
			{
				$regex = preg_match('/([0-9]+)/',$node->getAttribute("href"),$matches);
				$urls[] = "http://www.nvaonline.com/hospital_details/".$matches[1];
			}
			log::info($urls);
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$data = array();
			foreach ($x->query("//div[@class='hosp_name']") as $node)
			{
				$data['NAME'] =$node->textContent;
				$address =$node->nextSibling->nextSibling->textContent.", ".$node->nextSibling->nextSibling->nextSibling->nextSibling->textContent;
				$data = array_merge($data, $ap->parse($address));
			}

			foreach ($x->query("//b") as $node)
			{
				$number = $pp->parse($node->textContent);
				if (isset($number['PHONE']))
				{
					$phoneType = preg_replace("/[^a-z]/i", "", $node->nextSibling->textContent);
					$data[$phoneType] = $number['PHONE'];
				}
			}

			foreach ($x->query("//a[@target='_blank']") as $node)
			{
				$data['WEBSITE'] =$node->getAttribute("href");
			}

			foreach ($x->query("//a[contains(@href,'mailto:')]") as $node)
			{
				$data['EMAIL'] = str_replace("mailto:", "", $node->getAttribute("href"));
			}


			if (!empty($data['NAME']))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('NAME','PHONE','ZIP'));	
			}
		
		}

	

	}
}

$r= new nvaonline();
$r->parseCommandLine();

