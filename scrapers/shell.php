<?
include_once "config.inc";

class shell extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		#db::query("DELETE FROM load_queue");
		#db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		#db::query("DELETE FROM raw_data");
		#db::query("DROP TABLE  $type");
		#db::query("DELETE FROM $type");

		
		$this->threads=2;

		$this->debug=false;
		//$this->loadUrl("http://www.localshell.com/SearchResults.aspx?SearchType=AutoStation&txtOriginStreet=&txtOriginCityStateOrZip=&txtOriginPlace=94928&txtDestinationStreet=&txtDestinationCityStateOrZip=&txtDestinationPlace=&ddlDistanceFromCenter=20&ddlStationsPerPage=10");
		#$this->loadUrlsByZip("http://www.localshell.com/SearchResults.aspx?SearchType=AutoStation&txtOriginStreet=&txtOriginCityStateOrZip=&txtOriginPlace=%ZIP%&txtDestinationStreet=&txtDestinationCityStateOrZip=&txtDestinationPlace=&ddlDistanceFromCenter=20&ddlStationsPerPage=21");
   }

	static function parse($url,$html)
	{
		
		$type = get_class();		
		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$html = str_replace("<br>","\n",$html);
		$html = str_replace("&middot;","|",$html);
		
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		
		
		foreach ($x->query("//form/table/tr") as $node)
		{		
			
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	


			foreach ($x2->query("//td[4]") as $node2)
			{		
				$data= array();
				$contact = explode("\n",$node2->textContent);

				if (array_key_exists(1,$contact))
				{					
					$citystatezip = $contact[1];
					
					if (preg_match("/(.+), ([A-Z][A-Z])  ([0-9]{5}-[0-9]{4})/", $citystatezip, $matches))
					{
						$data['address'] = $contact[0];
						$data['city'] = $matches[1];
						$data['state'] = $matches[2];
						$data['zipcode'] = $matches[3];

						if (array_key_exists(2,$contact))
						{
							$data['phone'] = $contact[2];
						}
					}
				}
			}

			if(!empty($data))
			{
				
				foreach ($x2->query("//td[5]") as $node2)
				{		
					$services = explode("|",str_replace("\n", "", $node2->textContent));

					array_shift($services);

					foreach($services as $service)
					{
						$service = str_replace("Redeem for Grocer:","",$service);
						$serviceKey = str_replace(" ","_",trim($service));
						$serviceKey = str_replace("&","and",trim($serviceKey));

						$data[$serviceKey] = $service;
					}			
				}				

				#print_r($data);
				log::info($data['zipcode']);
				
				db::store($type,$data,array('address','city','state','zipcode'),false);
				#
				# this is a 2-3x faster than using store()
				# db::insertInto($type,$data,false,true);

			}
			
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
$r = new shell();

//for($i=0;$i<5;$i++)
{
	$r->runLoader();
	$r->parseData();
}

$r->generateCSV();
