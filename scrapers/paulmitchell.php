<?
include_once "config.inc";

class paulmitchell extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		

		R::freeze();
		$type = get_class();		
	#db::query("DELETE FROM load_queue");
		#db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		#db::query("DELETE FROM raw_data");
		#db::query("DROP TABLE  $type");
		#db::query("DELETE FROM $type");

		
		$this->threads=2;

		$this->debug=false;

		$this->loadUrlsByZip("http://www.paulmitchell.com/en-us/search/Pages/SalonSearch.aspx?k=%ZIP%",0);

		//$this->loadUrl("http://www.paulmitchell.com/en-us/search/Pages/SalonSearch.aspx?k=94101");
		
		$this->queuedGet();
   }

	static function parse($url,$html)
	{
		
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		
		
		$i=0;
		foreach ($x->query("//table[@class='searchResult']/tr") as $node)
		{

			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	
			$data=array();

			foreach ($x2->query("//a[@class='salonName']") as $node2)
			{
				$data['name'] = $node2->textContent;
			}
			foreach ($x2->query("//a[@class='salonWeb']") as $node2)
			{
				$data['website'] = $node2->getAttribute('href');
			}

			//*[@id="ctl00_PlaceHolderMain_SalonLocator_SearchResult_ctl06_Phone"]

			foreach ($x2->query("//span[contains(@id,'_Phone')]") as $node2)
			{
				$data['phone'] = $node2->textContent;
			}

			foreach ($x2->query("//span[contains(@id,'_Address')]") as $node2)
			{
				$data['address'] = $node2->textContent;
			}

			foreach ($x2->query("//span[contains(@id,'_City')]") as $node2)
			{
				$data['city'] = $node2->textContent;
			}

			foreach ($x2->query("//span[contains(@id,'_State')]") as $node2)
			{
				$data['state'] = $node2->textContent;
			}

			foreach ($x2->query("//span[contains(@id,'_Label1')]") as $node2)
			{
				$data['zip'] = $node2->textContent;
			}

			foreach ($x2->query("//img[contains(@id,'_Emblem')]") as $node2)
			{
				$data['salonType'] = str_replace(".png","",baseName($node2->getAttribute("src")));
			}

			
			if (array_key_exists('name', $data))
			{
				log::info($data['name'].' '.$data['phone']);
			  db::store($type, $data);
			 
			 # this is a bit faster...
			 # db::replaceInto($type,$data);
			}
		}

		// CHECK IF THERE IS A NEXT LINK
		foreach( $x->query("//a[@id='ctl00_PlaceHolderMain_SalonLocator_Next']") as $node)
		{
			self::getInstance()->loadUrl("http://www.paulmitchell.com".$node->getAttribute('href')) ;
			break;
		}
		log::info ("Done parsing");

	}


	static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/","",$str);
	}
	
	static function cleanup($str)
	{
		while (preg_match("/  /",$str))
		{
			$str = preg_replace("/  /i"," ",$str);
		}
		return trim($str);
	}

}
$r = new paulmitchell();

//for($i=0;$i<5;$i++)
{
	$r->runLoader();
	$r->parseData();
}

$r->generateCSV();
