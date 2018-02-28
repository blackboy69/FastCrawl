<?
include_once "config.inc";


class napaautopro extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		

		// R::freeze();
		$type = get_class();		
		//db::query("DELETE FROM load_queue  where type='$type'");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		$this->threads=4;

		$this->debug=false;

		//$this->loadUrlsByZip("http://www.napaautopro-usa.com/_en/_us/conso/salons/salon_result.aspx?zipcode=%ZIP%&SearchFilter=&ispostback=1",0);

		
		$this->loadUrl("http://www.napaautopro.com//alberta.html");
		$this->loadUrl("http://www.napaautopro.com//british-columbia.html");
		$this->loadUrl("http://www.napaautopro.com//manitoba.html");
		$this->loadUrl("http://www.napaautopro.com//new-brunswick.html");
		$this->loadUrl("http://www.napaautopro.com//newfoundland-and-labrador.html");
		$this->loadUrl("http://www.napaautopro.com//nova-scotia.html");
		$this->loadUrl("http://www.napaautopro.com//ontario.html");
		$this->loadUrl("http://www.napaautopro.com//prince-edward-island.html");
		$this->loadUrl("http://www.napaautopro.com//quebec.html");
		$this->loadUrl("http://www.napaautopro.com//saskatchewan.html");

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
		
   }

	static function parse($url,$html)
	{
		
		$type = get_class();		
		
		$html = preg_replace("/<br.?\/>/","|",$html);
		$html = preg_replace("/<br>/","|",$html);
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		//echo $html ;
		//exit;
		
		// get the list of salons
		//*[@id="Table3"]
		foreach ($x->query("//div[@class='subResult MECHANICAL']") as $node)
		{

			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	
			$data=array();
			
			
			foreach ($x2->query("//h5") as $node2)
			{
				$data['name'] = trim($node2->textContent);
			}

			foreach ($x2->query("//address") as $node2)
			{
				$info = explode("|", trim($node2->textContent));

				$data['address'] = $info[0];				
				list($data['city'],$data['zip']) = explode(",",$info[1]);
				$data['phone'] = $info[2];
				$data['full_location'] = $node2->textContent;
			}
			$data['country'] = 'Canada';  

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
$r = new napaautopro();

//for($i=0;$i<5;$i++)
{
	$r->runLoader();
	$r->parseData();
}

$r->generateCSV();