<?
include_once "config.inc";

class browntoland extends baseScrape
{
    public static $_this=null;
	public static $google = null;
	public static $bing = null;	
	public static $csv=null;
	public static $csvMap=array();

	private $urlsToLoad = array();
	
   public function runLoader()
   {
		
		$type = get_class();		
		/*	db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
			//db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
		

		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");

		db::query("DROP TABLE $type");	
		
*/
		
		//$this->proxy = "localhost:8888";		
		$this->noProxy=true;

		// $this->threads=1;
		// $this->useCookies = true;
		// $this->timeout = 8;

		$url = "http://www.brownandtoland.com:9795/ProviderDirectory/Results?firstName=&lastName=&networks=false&ddlSpecialty=No+Preference&zipCode=&ddlPracticeLanguages=No+Preference&ddlOfficeLanguages=No+Preference&ddlPracticeStatus=No+Preference&ddlProviderType=No+Preference&ddlGender=No+Preference&siteLocation=";
		$this->loadUrl($url);
		$this->queuedGet();
	}
	
	static function parse($url,$html)
	{
		$t=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$urls = array();

		if (preg_match("/ProviderDirectory\/Results/",$url))
		{
			$header = true;
			$data = array();
			$x = new Xpath($html);
			foreach($x->query("//table[@id='provResults']|//tr") as $node)
			{
				if ($header)  {$header=false; continue;}

				$website = $href = "";
				$x2 = new Xpath($node);
				foreach($x2->query("//a") as $linkNode)
				{
					$link =  $linkNode->getAttribute("href");
					if (preg_match("/mapquest.com/", $link ))
						continue;
					else if (preg_match("/AddressDetails/",$link))
						continue;
					else if (preg_match("/providerDetails/", $link ))
						$href = self::relative2absolute($url, $link);
					else 
						$website = $link;					
				}

				$href .= "&website=".urlencode($website);
				$urls[] = $href;
				echo(".");
			}
			$t->loadUrlsByArray($urls);
		}
		else if (preg_match("/ProviderDirectory\/providerDetails/",$url))
		{
			$data = array();
			$data['Name'] = $query['physicianName'];
			$data['Website Url'] = $query["website"];
			$data['Source Url'] = $url;
			

			if (isset($data['Name']))
			{			
				$x = new Xpath($html);
				foreach($x->query("//div[@class='row']") as $node)
				{
					$x2 = new Xpath($node);
					
					$key = $value = "";
					foreach($x2->query("//div[@class='rowCellLabel']") as $keyNode)
					{
						$key =  str_replace(":","",self::cleanup($keyNode->textContent));
					}
					foreach($x2->query("//div[@class='rowCell']") as $valueNode)
					{
						$value = self::cleanup($valueNode->textContent);
					}

					if (!empty($key) && !empty($value))
					{
					
						// convert the list of addresses into fields like adddress_1, city_1 address_2 etc.
						if ($key == 'Addresses')
						{
							$x3 = new Xpath($valueNode);
							$i=1;
							foreach ($x3->query("//a") as $addrLink)
							{
								$link = $addrLink->getAttribute("href");
								if (preg_match("/mapquest.com/", $link ))
								{
									$mapQuery = array();
									parse_str(parse_url($link,PHP_URL_QUERY),$mq); // address and zip	

									$data['Address '.$i] = $mq['address'];
									$data['City '.$i] = $mq['city'];
									$data['State '.$i] = $mq['state'];
									$data['Zip '.$i] = $mq['zipcode'];
									$i++;
								}

							}
							//phone numbers
							$addresses = explode("webpres.png",$valueNode->c14n());
							for($i=0 ; $i<sizeof($addresses)-1 ; $i++)
							{
								$parsed = array();
								$parsed = array_merge($parsed, $pp->parse($addresses[$i]));
								foreach($parsed as $k => $v)
								{

									$data["$k ".(1+$i)] = $v;
								}
							}

						}
						else
						{
							$data[$key] = $value;
						}
					}
				}


				log::info($data);
				try {				
					 db::store($type,$data,array('Name', 'Url'));
				}
				catch(Exception $e)
				{
					log::error ("Cannot store ".$data['Name']);
					log::error($e);
					//print_r($data);
					exit;
				}		
			}
		}
		else
		{
			log::Error("Unknown url");
		}
	}

		static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/"," ",$str);
	}
	
	static function cleanup($str)
	{
		while (preg_match("/  /",$str))
		{
			$str = preg_replace("/  /i"," ",$str);
		}
		return trim(self::removeSpaces($str));
	}
}
$r = new browntoland();
$r->parseCommandLine();
