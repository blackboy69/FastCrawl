<?
include_once "config.inc";

class midas extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type'");
		#db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		#db::query("DELETE FROM raw_data");
		#db::query("DROP TABLE  $type");
		#db::query("DELETE FROM $type");

		
		$this->threads=4;

		$this->debug=false;
		//$this->loadUrl("http://1699airlinehighway.midassanfrancisco.com/store.aspx?shopNum=1167&language=en-US");
		$this->loadUrlsByZip("http://www.midaslocator.com/index.aspx?zipPostalCode=%ZIP%",0);
   }

	static function parse($url,$html)
	{
		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$html = str_replace("<br>","\n",$html);
		
		
		$path = parse_url($url,PHP_URL_PATH);

		if (preg_match("/area.aspx/",$path))
		{
			self::parseListings($url,$html);
		}
		else if (preg_match("/store.aspx/",$path))
		{
			// parse listing.
			self::parseDetails($url,$html);
		}
	}
	
	static function parseListings($url,$html)
	{
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		
		
		foreach ($x->query("//div[@class='lo_address']//a") as $node)
		{
			$href = $node->getAttribute("href");
			$path = parse_url($href,PHP_URL_PATH);

			if ( preg_match("/store.aspx/",$path))
			{
				echo ".";
				self::getInstance()->loadUrl($href);			
			}
		}

	}

	static function parseDetails($url,$html)
	{
		$html = str_replace("&nbsp;"," ",$html);
		$html = str_replace("\t"," ",$html);
		$html = str_replace("├é┬á"," ",$html);
		$type = get_class();		

		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		
		$data = array();

		foreach ($x->query("//div[@class='shopDetails']//div[@class='fn org']") as $node)
		{	
			$data['name'] = self::reduceExtraSpaces($node->textContent);
		}

		foreach ($x->query("//div[@class='shopDetails']//div[@class='tel']") as $node)
		{	
			$data['telephone'] = $node->textContent;
		}

		foreach ($x->query("//div[@class='shopDetails']//div[@class='street-address']") as $node)
		{				
			$data['address'] =  $node->textContent;
		}

		foreach ($x->query("//div[@class='shopDetails']//div[@class='region']") as $node)
		{
			$text = $node->textContent;
			if (preg_match("/(.+),.+([A-Z][A-Z]).+([0-9]{5})/",  $text, $matches))
			{
				$data['city'] = $matches[1];
				$data['state'] = $matches[2];
				$data['zipcode'] = $matches[3];
			}			
		}

		foreach ($x->query("//div[@class='shopDetails']//p[@class='hours']") as $node)
		{
			foreach (self::parseHours($node->textContent) as $day => $hours)
			{
				$data[$day] = $hours;
			}
		}
		
		log::info($data['name']);
		db::insertInto($type,$data,false,true);
		//db::store($type,$data,array('name','telephone'.'address'),false);

		

	}

	static function parseHours($text)
	{
		$days = array("Monday"=>1,"Tuesday"=>2,"Wednesday"=>3,"Thursday"=>4,"Friday"=>5,"Saturday"=>6,"Sunday"=>7);
		$daysRev = array_flip($days);
		$data = array();

		// will take Monday - Friday and return array("Monday","Tuesday","Wednesday","Thursday","Friday")
		if (preg_match("/^([a-z]+) - ([a-z]+) (.+)/i",$text, $matches))
		{		
			for($i = $days[$matches[1]]; $i<= $days[$matches[2]] ; $i++)
			{
				$data[$daysRev[$i]] = $matches[3];
			}
		}
		else if (preg_match("/^([a-z]+) (- )?(.+)/i",$text, $matches))
		{
			$data[$matches[1]] = $matches[3];
		}
		return $data;
	}

	static function reduceExtraSpaces($str)
	{
			return trim(preg_replace("/(\t|\n|\r| )+/"," ",$str));
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
$r = new midas();

//for($i=0;$i<5;$i++)
{
	$r->runLoader();
	$r->parseData();
}

$r->generateCSV();
