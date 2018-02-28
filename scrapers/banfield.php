<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class banfield extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		//$this->noProxy=false;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		db::query("DROP TABLE $type");
	/*	
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		
			
	
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	
*/	


		$this->loadUrlsByZip('http://www.banfield.com/Pet-Owners/Our-Hospitals/Locations/View-By-Metro');
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	

		// get metros
		$urls = array();
		foreach($x->query("//ul[@class='state-list clearfix']//a") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		if (!empty($urls))
		$thiz->loadUrlsByArray($urls);	
		
		/// get details
		$urls = array();
		foreach($x->query("//div[@class='hospital-location-info']//a") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		if (!empty($urls))
		$thiz->loadUrlsByArray($urls);				

	
		//details
		if (strpos($url, "/Location-Pages/") > 0)
		{
			
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();


			$data = array();

			foreach ($x->query("//li[@class='org fn title']") as $node)
			{
				$data['NAME'] = $node->textContent;
			}
			foreach ($x->query("//li[@class='nickname']") as $node)
			{
				$data['NICKNAME'] = $node->textContent;
			}
			foreach ($x->query("//li[@class='adr']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
			}
			foreach ($x->query("//li[@class='tel']") as $node)
			{
				$data['PHONE'] = $node->textContent;
			}
			
			$services = array();
			foreach ($x->query("//div[@class='grayDot']/following-sibling::ul/li") as $node)
			{
				$services[] = $node->textContent;
			}
			$data["SERVICES_OFFERED"] =  join(", ",$services);


			foreach ($x->query("//*[contains(text(),'Hospital Number:')]") as $node)
			{
				$data['HOSPITAL_NUMBER'] = str_replace("Hospital Number:","",trim($node->textContent));
			}

			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			
		}
	}
}

$r= new banfield();
$r->parseCommandLine();

