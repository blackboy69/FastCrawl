<?
include_once "config.inc";

class aoa extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		#db::query("DELETE FROM load_queue where type='$type' ");
		#db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		#db::query("DELETE FROM raw_data where type='$type' ");
		#db::query("DELETE FROM $type");

		
		$this->threads=4;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
		#unlink ($this->cookie_file);
		$this->useCookies=true;
		// $this->login();


		$this->loadUrlsByCity("http://www.aoa.org/prebuilt/DrLocator/searchResults.asp?hidOption=2&txtZipcode=&selMileage=100&txtCity=%CITY%&txtState=%STATE%&txtDocLN=&txtDocFN=&txtPracName=&selPracEmph=&rdoInfantSee=n&rdoSection=&pageNum=1");

   }

	static function parse($url,$html)
	{		
		if (preg_match("/searchDetail.asp/", $url))
		{
			return self::parseDetails($url,$html);
		}
		else if (preg_match("/searchResults.asp/",$url))
		{
			return self::parseListings($url,$html);
		}
	}	

	static function parseListings($url,$html)
	{
		// parse the listsings of jobs page. 
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		
		
		$href = "";
		foreach($x->query("//a") as $node)
		{

			if (preg_match("/searchDetail.asp/", $node->getAttribute("href")))
			{
				$href = preg_replace("/ /","+",self::relative2absolute($url, $node->getAttribute("href")));
			} 
			else if (preg_match("/Next Page/", $node->textContent))
			{
				$href = preg_replace("/ /","+",self::relative2absolute($url, $node->getAttribute("href")));				
			}
			
			if (! empty($href) )
			{
				self::getInstance()->loadUrl($href);
				#log::info("Mock loading $href");
			}
		}		
	}


	static function parseDetails($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$html = preg_replace("/&[^;]+;/", "", $html);
		

		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	

		foreach($x->query("//span[@class='displayName']") as $node)
		{
			$data['Name'] = $node->textContent;			
		}

		foreach($x->query("//span[@class='title']") as $node)
		{
			$name = str_replace(" ","_", str_replace(":", "", $node->textContent));
			$name = preg_replace("/[^0-9a-zA-Z_]/","",$name);
			
			$data[$name] = "";
			$value = $address = array();

			$i=0;
			while ( ($node = $node->nextSibling) != null)
			{	
				if (empty($node->textContent))
				{
					continue;
				}

				if ($node->nodeType == XML_ELEMENT_NODE && $node->getAttribute("class") == 'title')
				{
					break;
				}

				if ($name == "Primary_Practice_Location")
				{
					$address[] = $node->textContent;
				}
				
				$value[] = $node->textContent;
				
				if ($i++ > 10)
					break;
			}
			$data[$name] = join(" ",$value);

			if(!empty($address))
			{
				//first element is the street address
				$data['address'] = $address[0];
				if (preg_match("/(.+),\s*([A-Z][A-Z])\s*([0-9]{5})/", $address[1],$matches))
				{
					$data['city'] = $matches[1];
					$data['state'] = $matches[2];
					$data['zip'] = $matches[3];
				}


			}
			
		}
		
		if (! empty($data['Name']))
		{
			print_r($data);
			db::store($type, $data,array('Name','Phone'),false);
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
$r = new aoa();
$r->ParseCommandLine();
