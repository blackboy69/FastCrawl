<?
include_once "config.inc";

class spiceworks_com extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		
		$type = get_class();	

		//ProductSearch.cb.callback0("pages":199,"total":39618,"cur_page":1,"results":
		// could parse to get the actual pages, but brute force it is good enough
		for($i=0;$i<225;$i++)
		{
			$urls[] = "http://community.spiceworks.com/api/v2/catalog/search.json?&p=$i&q=%7B%22keywords%22%3A%22*%22%2C%22facets%22%3A%5B%22services%3Band%22%5D%2C%22filter_variants%22%3Atrue%2C%22index%22%3A%22msp_directory%22%2C%22type%22%3A%22msp_profile%22%2C%22sort%22%3A%7B%22field%22%3A%22popularity%22%2C%20%22dir%22%3A%22desc%22%7D%7D&ipp=250&callback=ProductSearch.cb.callback0";
		}

		db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1 and url not like 'http://community.spiceworks.com%' ");
//		db::query("DROP TABLE $type");	
		
		$this->timeout = 5;
		$urlsToLoad = array();
		$this->maxRetries = 1;

		$this->loadUrlsByArray($urls);
	}
	
	static function parse($url,$html)
	{
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$host = parse_url($url,PHP_URL_HOST);

		if ($host =="community.spiceworks.com")
		{		
			$jsonString = preg_replace("/^ProductSearch.cb.callback0\((.+)\)$/","\\1",$html);
			$json = json_decode($jsonString,true);
			foreach ($json['results'] as $d=> $data)
			{
				$services = $data['services'];
				$locations = $data['locations'];
				$data['XID'] = $data['id'];			
				

				unset ($data['services']);
				unset ($data['locations']);
				unset ($data['id']);

				unset ($data['description']); // makes file too big
				unset ($data['rates']); // makes file too big
				unset ($data['references']); // makes file too big


				$data['services'] = join(", ", $services);			
				foreach($locations as $location)
				{
					if($location['primary'])
					{
						unset($location['pin']);
						$data = array_merge($location,$data);
					}
				}		

				$data['SOURCE_URL'] = $url;				
				echo ".";

				if (!empty($data['company_url']))
				{
					log::info("Found {$data['company_url']}");
					$thiz->loadUrl($data['company_url']."?XID=".$data['XID']);
				}
				db::store($type,$data,array('XID'));
			}
		}
		else // spidering for email addresses
		{

			if (empty($query['XID']))
			{
				log::error("Bad happend!");
				return;
			}

			$x = new Xpath($html);
			$ep = new Email_Parser();
			
			foreach( $x->query("//html") as $node)
			{
				// find email addresses. stop spidering if we found one.
				$emails = $ep->parse($node->textContent);
			}

			if (empty($emails))
			{			
				$urls = array();
				log::info("No emails found, no spidering.");
				// spider for more listings 
				foreach($x->query("//a[contains(@href,'$host')]") as $node)
				{
					$urls[] = $node->getAttribute("href");	
				}

				// local references
				// spider for more listings 
				foreach($x->query("//a[not(contains(@href,'http'))]") as $node)
				{
					$urls[] = $node->getAttribute("href");	
				}

				//$this->loadUrlsByArray($urls);
			}
			else
			{

				log::info("Found emails!");
				log::info($emails);

				// load the entity
				$data = db::query("SELECT * FROM $type where XID = {$query['XID']}");
				$data = array_merge($data, $emails);
				db::store($type,$data,array('XID'),true);
				log::info($data);
			}

			
			

		}
	}
}

$r = new spiceworks_com();
$r->parseCommandLine();
