<?
include_once "config.inc";
include_once "search_engine_google.php";

class nysdental extends baseScrape
{
   public static $_this=null;
	public $google = null;
	public $i = 0; //debug;


	public function runLoader()
   {

		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type'");
		db::query("UPDATE load_queue set processing = 0 where type='$type' ");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DELETE FROM $type");
		db::query("delete from raw_data where length(html) < 1600 and type ='nysdental'");
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
		$this->threads=1;
		
		$this->timeout = 5;
		//$this->debug=true;
		// when posting the system sucks and only loads one url. make the url unique by passing the zip code in the ID field
		$this->loadUrlsByStateZip("http://%ZIP%@www.nysdental.org/find_a_dentist/searchResults.cfm?zip=%ZIP%+&expertise=&radius=1&keyword=Dentist+Name+%28optional%29&x=47&y=11&=submit","NY","http://www.nysdental.org/");

		//$url = "http://www.nysdental.org/find_a_dentist/searchResults.cfm?zip=12211+&expertise=&radius=25&keyword=Dentist+Name+%28optional%29&x=47&y=11&=submit";
		//$this->setReferer($url,"http://www.nysdental.org/");


				R::freeze();
		$this->queuedPost();		
   }
	
	static function parse($url,$html)
	{
		$type = get_class();	
		#$html = preg_replace("/(\t|\n|\r)+/"," ",$html);

		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$data=array();            

		foreach ($x->query("//table[@width='552']//tr") as $node)
		{
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);				
		
			foreach ($x2->query("//td[2]") as $node2)	
			{
				$data['Name'] = trim($node2->textContent);
			}

			if (empty($data['Name']))
			{
				continue;
			}

			if ($data['Name'] == 'DENTIST:')
			{
				continue;
			}


			foreach ($x2->query("//td[3]") as $node2)	
			{
				$address =  trim($node2->textContent);
				$data['Full_Address']  =  preg_replace("/(\t|\n|\r)+/"," ",$address);
				
				list($line1,$line2) = explode("\n",$address);
				$data['Address'] = trim($line1);

				if (preg_match("/(.+),.+([A-Z][A-Z]).+([0-9]{5}(-[0-9]{4})?)/",  $line2, $matches))
				{
					$data['City'] = trim($matches[1]);
					$data['State'] = trim($matches[2]);
					$data['Zipcode'] = trim($matches[3]);
				}	

			}

			foreach ($x2->query("//td[4]") as $node2)	
			{
				$data['Phone'] = trim($node2->textContent);
			}

			foreach ($x2->query("//td[5]") as $node2)	
			{
				$data['Specialty'] = trim($node2->textContent);
			}
			
		
			
			if (!empty($data['Name']))
			{
				log::info($data['Name']);
				db::store($type,$data,array('Name','Phone'),false);
			}
			//db::insertInto($type,$data,false,true);
		}
	}

	
	
	function parseHours($text)
	{
		$days = array("Mon"=>1,"Tue"=>2,"Wed"=>3,"Thu"=>4, "Fri"=>5,"Sat"=>6,"Sun"=>7);
		
		$daysAll= array("Mon"=>1,"Tue"=>2,"Wed"=>3,"Thu"=>4, "Fri"=>5,"Sat"=>6,"Sun"=>7,"Monday"=>1,"Tuesday"=>2,"Wednesday"=>3,"Thursday"=>4,"Thur"=>4,"Friday"=>5,"Saturday"=>6,"Sunday"=>7);

		$daysRev = array_flip($days);
		$data = array();

		
		// will take Monday - Friday and return array("Monday","Tuesday","Wednesday","Thursday","Friday")
		if (preg_match("/([a-z]+)-([a-z]+) (.+)/i",$text, $matches))
		{		
			$fromTime = trim($matches[1]);
			$toTime = trim($matches[2]);
			for($i = $daysAll[$fromTime]; $i<= $daysAll[$toTime] ; $i++)
			{
				$data[$daysRev[$i]] = trim($matches[3]);
			}
		}
		else if (preg_match("/^([a-z]+) (- )?(.+)/i",$text, $matches))
		{
			$data[$matches[1]] = trim($matches[3]);
		}
		return $data;
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
$r = new nysdental();

$r->runLoader();
$r->parseData();
$r->generateCSV();
$r->saveZip("C:\\dev\\htdocs\\demandforce\\dentists");

log::info("Parsed $r->i urls");
