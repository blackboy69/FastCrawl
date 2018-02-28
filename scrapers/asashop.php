<?
include_once "config.inc";


class asashop extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		

		//R::freeze();
		$type = get_class();		
		//db::query("DELETE FROM load_queue  where type='$type'");
		//db::query("DELETE FROM raw_data  where type='$type'");
		 db::query("UPDATE raw_data set parsed = 0 where type='$type' ");


		db::query("DROP TABLE  $type");
		//db::query("DELETE FROM $type");

	

		$this->threads=10;

		$this->debug=false;

			$url = "http://www.asashop.org/maptest/advantage.asp?transaction=search&country=us&template=map_search&maxSearchResults=100&pageResults=100&address=&city=&stateProvince=&postalCode=%ZIP%&radius=20&x=90&y=2";
			

			//$this->loadUrl("http://www.asashop.org/maptest/advantage.asp?transaction=search&country=us&template=map_search&maxSearchResults=140&pageResults=100&address=&city=&stateProvince=&postalCode=94134&radius=20&x=90&y=2");
			$this->loadUrlsByZip($url);
   }

	static function parse($url,$html)
	{
		$a = new Address_Parser();
		$oh = new Operating_Hours_Parser();

		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);

		$x = new HtmlParser($html);	
		$data = array();

		
		
		// get the list of salons
		//*[@id="Table3"]
		foreach ($x->query("/html/body/table/tr[3]/td/table/tr[10]/td/table/tr/td[5]") as $node)
		{
			$listingHtml = $node->c14n();
			

			$data = array();
			$data['Raw Listing'] = $listingHtml;

			$x1 = new HtmlParser($listingHtml);	
			foreach( $x1->query("//span[@class='mqEmp']") as $nameNode)
			{
				$data['Name'] =$nameNode->textContent;
			}

			if (!isset($data['Name']))
			{
				continue;
			}

			list($address,$rest) = explode("Phone:",$listingHtml);
			$data = array_merge($data, $a->parse($address));	

			// grab any url

			if ( preg_match('/href="(http[^"]+)"/',$listingHtml,$matches) )
			{				
					$data['Url'] = $matches[1];				
			}

			
			// now get meta data...

			$metadata = explode("<b>",$listingHtml);

			for($i=1;$i<sizeof($metadata);$i++)
			{
				
				$md = preg_replace("/([a-zA-Z]):/","$1 : ",$metadata[$i]);
				
				list($key,$value) = preg_split("/ : /",$md);

				$data[$key] = $value= trim(preg_replace("/(<[^>]+>)+/","",$value));
/*
				if ($key == "Services" || $key == "Accepted Methods of Payment")
				{
					foreach(explode(",",$value) as $service)
					{
						$service=trim($service);
						if (!empty($service))
							$data[$service] = "Yes";
					}
				}*/
			}

			
			$data = array_merge($data, $oh->parse($data["Hours of Operation"]));	
			
			// remove undesired columns
			unset($data["Raw Address"]);
			unset($data["Full Hours"]);

			// cleanup

			$data['Phone'] =str_replace('<a href="http','',$data['Phone']);

			
			
			log::info($data);
			db::store($type, $data,array('Name', 'Phone'),false);		 

			// tags to spaces
			//$html2 = preg_replace("/(<[^>]+>)+/"," ",$html);
			
		//	log::info($html2);
			// replace any tag by spaces
		}


		if (!empty($data['name']))
		{
			//log::info($data['name']);
			//db::store($type, $data,array('name', 'full_address'),false);		 
		}
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
$r = new asashop();

$r->runLoader();
$r->parseData();


$r->generateCSV();
$r->saveZip("C:\\dev\\htdocs\\demandforce\\auto");

print_r($nomatch);