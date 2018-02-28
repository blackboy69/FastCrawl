<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class obagi extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		//$this->noProxy=false;
		//$this->proxy = "localhost:8888";

		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
/*		
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
db::query("DROP TABLE $type");
	
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		
			
	
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	
*/	


		$this->loadUrlsByZip('http://www.obagi.com/patients/find-obagi?country=United+States&postal=%ZIP%&physician=&distance=100&submit=Search+Now',40000);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$xTop = new XPath($html);	

		// get next links
		$urls = array();
		foreach($xTop->query("//ul[@class='pager']//a") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		if (!empty($urls))
			$thiz->loadUrlsByArray($urls);	
		
	
		//details
				
		$ap = new Address_Parser();
/*		$op = new Operating_Hours_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();*/


		foreach($xTop->query("//*[contains(@class,'found')]") as $listingNode)
		{
			$data = array();
			$x = new XPath($listingNode);

			foreach ($x->query("//div[@class='name']") as $node)
			{
				@list($name,$practice) = explode("<br>",$node->c14n());
				$data['NAME'] = html_entity_decode(self::cleanup(strip_tags($name)));
				$data['PRACTICE'] = html_entity_decode(self::cleanup(strip_tags($practice)));
				
			}

			foreach ($x->query("//div[@class='left']//div[@class='info']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
			}


			foreach ($x->query("//div[@class='right']//strong") as $node)
			{
				$data['PHONE'] =  self::cleanup($node->textContent);
			}


			if (!empty($data))
			{
				unset ($data['RAW_ADDRESS']);
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('NAME','ADDRESS', 'PHONE'));	
			}		
		}
	}
}

$r= new obagi();
$r->parseCommandLine();

