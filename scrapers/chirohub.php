<?
include_once "config.inc";

class chirohub extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		#db::query("DELETE FROM load_queue where type='$type' ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		#db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");
		db::query("DELETE FROM $type");
		
		

		
		$this->threads=1;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
//		unlink ($this->cookie_file);
	//	$this->useCookies=true;
		//$this->login();
		//$this->loadUrl("https://www.chirohub.org/member/findadentist_results.aspx?fragmentid=166&lastname=&city=&withinmiles=0&zipcode=01230&practicetype=AllSpecialties&foreignlanguage=AllLanguages");

		$this->loadUrl("http://www.chirohub.com/index.php?option=com_mtree&task=listcats&cat_id=67&Itemid=103");
		#$this->loadUrl("http://www.chirohub.com/index.php?option=com_mtree&task=listcats&cat_id=7554&Itemid=103");
		
   }
	

	static function parse($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$addressParser = new Address_Parser();

		$found  = false;
		
		foreach ($x->query("//div[@id='subcats']/ul/li") as $listing)
		{
			log::info($listing->textContent);
			//don't load pages with 0 listings
			if (! strstr($listing->textContent,"(0)"))
			{
				$dom2 = new DOMDocument();
				@$dom2->loadHTML($listing->c14n());
				$x2 = new DOMXPath($dom2);	
				foreach ($x2->query("//a") as $node)
				{
					log::info("Loading $listing->textContent");
					self::getInstance()->loadUrl("http://www.chirohub.com/".$node->getAttribute("href"));
					$found = true;
				}
			}
		}

		if (!$found)
		{

			foreach ($x->query("//div[@class='listing-summary']") as $listing)
			{
				$data=array();
				$dom2 = new DOMDocument();
				@$dom2->loadHTML($listing->c14n());
				$x2 = new DOMXPath($dom2);	
				foreach ($x2->query("//h3") as $node)
				{
					$data['Name'] = $node->textContent;
				}
				foreach ($x2->query("//div[@class='address']") as $node)
				{
					$data = array_merge($data, $addressParser->parse($node->textContent));
				}
				foreach ($x2->query("//div[@class='fieldRow']") as $node)
				{
					$dom3 = new DOMDocument();
					@$dom3->loadHTML($node->c14n());
					$x3 = new DOMXPath($dom3);	

					
					foreach ($x3->query("//span[@class='caption']") as $node3)
					{
						$key = $node3->textContent;
					}
					foreach ($x3->query("//span[@class='output']") as $node3)
					{

						$value = $node3->textContent;
					}

					if ($key == "E-mail")
					{
						if (preg_match("/':'\+'(.+)\"/",$value,$matches))
						{
							$value = urldecode($matches[1]);
						}
					}
					$data[$key] = $value;
				}


				log::info($data);								
				db::store($type,$data,array('Name','Telephone'),false);
			}

			
		}
	}


	static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/","",$str);
	}
	
}
$r = new chirohub();
$r->runLoader();
$r->parseData();
$r->generateCSV();
