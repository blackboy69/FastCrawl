<?
include_once "config.inc";

class chiropractor_pages extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		

		//R::freeze();
		$type = get_class();		
		//db::query("DELETE FROM load_queue  where type='$type'");
		//db::query("DELETE FROM raw_data  where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		//db::query("DROP TABLE  $type");
		//db::query("DELETE FROM $type");
		
		/*
		db::query("		
		UPDATE load_queue
		 
		 SET processing = 0

		 WHERE			 
			 url NOT IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) > 1250)
			 AND type ='$type'
		");

		
		db::query("		
		UPDATE load_queue
		 
		 SET processing = 1

		 WHERE			 
			 url  IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) > 1250)
			 AND type ='$type'
		");
*/
		$this->threads=4;
		$this->noProxy=true;
		$this->debug=false;

		$url = "http://www.chiropractor-pages.com/include/ajax/getClients.php";
		$this->loadUrl($url);


		$this->queuedGet();
   }
/*
(
    [location] => stdClass Object
        (
            [country] => USA
        )

    [label] => USA
    [zoomLevel] => 3
    [children] => Array
        (
            [0] => stdClass Object
                (
                    [label] => Alberta
                    [location] => stdClass Object
                        (
                            [state] => Alberta
                        )

                )

            [1] => stdClass Object
                (
                    [label] => Alaska
                    [location] => stdClass Object
                        (
                            [state] => Alaska
                        )
*/
	static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY));
		$loadCountries = (!isset($city) && !isset($state) && !isset($country));
		$loadState = (!isset($city) && isset($state) && isset($country));
		$loadCities = (!isset($city) && isset($state) && isset($country));
		$loadListing = (isset($city) && isset($state) && isset($country));

		$json = json_decode($html);
		
		$urls = array();
		$dataTable=array();

		// load pages
		if (isset($json->childrenCount))
		{
			$total = $json->childrenCount;
			$pageSize= $json->childrenPerPage;
			for($page = 1;$page < ceil($total/$pageSize);$page++)
			{
				$urls[] = "http://www.chiropractor-pages.com/include/ajax/getClients.php?country=$country&state=$state&city=$city&page=$page";
			}
		}
		self::getInstance()->loadUrlsByArray($urls);

		$urls = array();

		// load states
		foreach($json->children as $node)
		{
			if($loadListing)
			{
				// listings

				
				if (isset($node->profile) && isset($node->location)) 
				{
					$data = array();
					$data['Name'] = $node->label;	
					$data = array_merge($data,self::object_to_array($node->location));
					$data = array_merge($data,self::object_to_array($node->profile));
					$data['specialties'] = join(", ", $data['specialties']);
					$data['xid'] = $data['id'];
					unset($data['id']);
					log::info($data['zip'] . " " . $data['Name'] );
					db::store($type, $data, array('xid'));

					
				}				
				
			}
			

			else if(isset($node->location->city) && isset($node->location->state) && isset($node->location->country))
			{
				$city =  urlencode($node->location->city);
				$state =  urlencode($node->location->state);
				$country =  urlencode($node->location->country);
				$urls[] = "http://www.chiropractor-pages.com/include/ajax/getClients.php?country=$country&state=$state&city=$city";
			}
		 

			else if(isset($node->location->state) && isset($node->location->country))
			{
				$state =  urlencode($node->location->state);
				$country =  urlencode($node->location->country);
				$urls[] = "http://www.chiropractor-pages.com/include/ajax/getClients.php?country=$country&state=$state";
			}
		
			

			else if(isset($node->location->country))
			{
				$country = urlencode($node->location->country);
				$urls[] = "http://www.chiropractor-pages.com/include/ajax/getClients.php?country=$country";
			}
		
			
		}
		self::getInstance()->loadUrlsByArray($urls);
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

	# Convert a stdClass to an Array.
	static public function object_to_array(stdClass $Class){
		# Typecast to (array) automatically converts stdClass -> array.
		$Class = (array)$Class;
	   
		# Iterate through the former properties looking for any stdClass properties.
		# Recursively apply (array).
		foreach($Class as $key => $value){
			if(is_object($value)&&get_class($value)==='stdClass'){
				$Class[$key] = self::object_to_array($value);
			}
		}
		return $Class;
	}
   
	# Convert an Array to stdClass.
	static public function array_to_object(array $array){
		# Iterate through our array looking for array values.
		# If found recurvisely call itself.
		foreach($array as $key => $value){
			if(is_array($value)){
				$array[$key] = self::array_to_object($value);
			}
		}
	   
		# Typecast to (object) will automatically convert array -> stdClass
		return (object)$array;
	}

}

$r = new chiropractor_pages();
$r->parseCommandLine();

