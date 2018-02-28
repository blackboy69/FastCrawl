<?
include_once "config.inc";


class benzshops_bimmershops extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		

		// R::freeze();
		$type = get_class();		
		//db::query("DELETE FROM load_queue  where type='$type'");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		#db::query("DROP TABLE $type");

		$this->threads=4;

		$this->debug=false;

		$this->sendQueryStringOnPost = true;
		$this->loadUrlsByZip("http://www.benzshops.com/search/results?address=%ZIP%",50000);
		$this->loadUrlsByZip("http://www.bimmershops.com/search/results?address=%ZIP%",50000);
		/*
		db::query("		
		UPDATE load_queue
		 
		 SET processing = 1

		 WHERE
			 url IN (SELECT url FROM raw_data WHERE type ='$type')
			 AND
			 type ='$type'
		");
		*/
		
		$this->queuedPost();
   }

	static function parse($url,$html)
	{
		
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		
		
		// get the list of salons
		//*[@id="Table3"]
		foreach ($x->query("//div[@class='result-item']") as $node)
		{

			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	
			$data=array();

			foreach ($x2->query("//h2") as $node2)
			{
				$data['name'] = trim($node2->textContent);
			}

			foreach ($x2->query("//span[@class='result-address']") as $node2)
			{
				$address = trim($node2->textContent);
				if (preg_match("/(.+), (.+), ([A-Z][A-Z])/",  $address, $matches))
				{
					$data['address'] = trim($matches[1]);
					$data['city'] = trim($matches[2]);
					$data['state'] = trim($matches[3]);
				}	
			}

			foreach ($x2->query("//span[contains(@id,'phone-')]") as $node2)
			{
				$data['phone'] = trim($node2->textContent);
			}

			
			if (array_key_exists('name', $data))
			{

				print_r($data);
				db::store($type, $data,array('name','phone'),false);

			 
			  # this is a bit faster...
			  # db::replaceInto($type,$data);
			}
		
		}


		echo ".";

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
global $nomatch;
$nomatch = array();
$r = new benzshops_bimmershops();

//for($i=0;$i<5;$i++)
{
	$r->runLoader();
	$r->parseData();
}

$r->generateCSV();