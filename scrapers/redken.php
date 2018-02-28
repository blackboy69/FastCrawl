<?
include_once "config.inc";
include_once "search_engine_google.php";

class redken extends baseScrape
{
   public static $_this=null;
	public $google = null;
	public $i = 0; //debug;


	public function runLoader()
   {
		$type = get_class();		
//		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
/*
		
		
		db::query("UPDATE load_queue set processing = 0 where type='$type' ");
		
		//db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DELETE FROM $type");
		db::query("delete from raw_data where length(html) < 1600 and type ='redken'");
		db::query("		
		UPDATE load_queue
		 
		 SET processing = 1

		 WHERE
			-- url IN (SELECT url FROM raw_data WHERE type ='$type')
			-- AND
			 type ='$type'
		");
	
//R::Freeze(true);
		$this->proxy = "localhost:9666";
		*/
		//$this->threads=3;
		
		//$this->timeout = 5;
		//$this->debug=true;

		//db::query("DELETE FROM load_queue where type='$type'");



		// when posting the system sucks and only loads one url. make the url unique by passing the zip code in the ID field
		$this->loadUrlsByCity("http://www.redken.com/salon_finder?search=%CITY%,%STATE%&search_radius=75&services[]=certified_colorist&services[]=chemistry_system&services[]=color&services[]=color_camo&services[]=haircare&services[]=men&services[]=styling&x=0&y=0");

		//$url = "http://www.redken.org/find_a_dentist/searchResults.cfm?zip=12211+&expertise=&radius=25&keyword=Dentist+Name+%28optional%29&x=47&y=11&=submit";
		//$this->setReferer($url,"http://www.redken.org/");


   }
	
	static function parse($url,$html)
	{
		$type = get_class();	
		#$html = preg_replace("/(\t|\n|\r)+/"," ",$html);

		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		  

		foreach ($x->query("//li[contains(@id,'item_')]") as $node)
		{
			$data=array();          

			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);				
		
			foreach ($x2->query("//div[@class='salon-information']/strong") as $node2)	
			{
				$data['name'] = trim($node2->textContent);
			}

			foreach ($x2->query("//div[@class='tel']") as $node2)	
			{
				$data['phone'] = trim($node2->textContent);
			}
			
			foreach ($x2->query("//div[@class='website']") as $node2)	
			{
				$data['website'] = trim($node2->textContent);
			}

			foreach ($x2->query("//span[@class='street-address']") as $node2)				
			{
				$data['address'] = trim($node2->textContent);
			}

			foreach ($x2->query("//span[@class='locality']") as $node2)	
			{
				$data['city'] = trim($node2->textContent);
			}

			foreach ($x2->query("//span[@class='region']") as $node2)	
			{
				$data['state'] = trim($node2->textContent);
			}

			foreach ($x2->query("//span[@class='postal-code']") as $node2)	
			{
				$data['zip'] = trim($node2->textContent);
			}

			foreach ($x2->query("//ul[@class='HorizList salon-services']/li") as $node2)	
			{
				$key = str_replace(" ","_", $node2->getAttribute("title"));

				$data[$key] = "Yes";
			}

				// CHECK IF THERE IS A NEXT LINK
			foreach( $x->query("//div[@class='pagination']//a[last()]") as $node)
			{
				#log::info ("Pagination ".$node->getAttribute('href'));
				self::getInstance()->loadUrl("http://www.redken.com".$node->getAttribute('href')) ;
				break;
			}
			$data['SOURCE_URL'] = $url;
			if (!empty($data['name']))
			{
				print_r($data);
				#log::info($data['name']);

				db::store($type,$data,array('name','phone'),false);
			}
			//db::insertInto($type,$data,false,true);
		}
	}
}
$r = new redken();
$r->ParseCOmmandLine();
