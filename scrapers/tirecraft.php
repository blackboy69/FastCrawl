<?
include_once "config.inc";

class tirecraft extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' ");
//		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
	//	db::query("DELETE FROM raw_data where type='$type' ");
//		db::query("DROP TABLE $type");
		//db::query("DELETE FROM $type");
		
		

		
		//$this->threads=2;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
		// start crawling from state list
		
			$this->loadPostUrl("http://tirecraft.com/find-a-tirecraft/ ","select-service=&search-lat=43.653226&search-lng=-79.38318429999998&search-distance=30000",true);
   }
	

	static function parse($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$found  = false;
		
		// parse listings now
		foreach ($x->query("//li[contains(@class,'result')]") as $node)
		{
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	
			$data = array();

			// only continue if we are on the right node
			foreach ($x2->query("//h3//a") as $aNode)
			{
				$data['NAME'] = preg_replace("/ View .+/", "", $aNode->textContent);
			}

			foreach ($x2->query("//address") as $aNode)
			{
				$data = array_merge($data,$ap->parse($aNode->c14n()));
			}

			foreach ($x2->query("//div[@class='contact']") as $aNode)
			{
				$data = array_merge($data,$pp->parse($aNode->c14n()));
			}
			
			$services = array();
			foreach ($x2->query("//ul[@class='services-list']//i[contains(@class,'active')]/following-sibling::span") as $aNode)
			{
				$services[] = $aNode->textContent;
			}
			$data['SERVICES'] = join(", ",$services);

			foreach ($x2->query("//h3//a") as $aNode)
			{
				$data['SOURCE_URL'] = $aNode->getAttribute("href");
			}

			$data['RAW_ADDRESS'] = 	$data['RAW_ADDRESS'] ;

			log::info(db::normalize($data));
			db::store($type,$data,array('NAME','ADDRESS','PHONE'),false);
			
		}
	}
}
$r = new tirecraft();
$r->parseCommandLine();


