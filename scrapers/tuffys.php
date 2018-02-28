<?
include_once "config.inc";

class tuffys extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		#db::query("DELETE FROM load_queue where type='$type' ");
		#db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		#db::query("DELETE FROM raw_data where type='$type' ");
		#db::query("DROP TABLE $type");
		#db::query("DELETE FROM $type");
		
		

		
		$this->threads=2;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
		// start crawling from state list
		
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,43/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,44/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,45/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,46/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,47/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,48/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,49/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,50/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,51/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,52/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,53/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,54/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,55/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,56/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,57/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,58/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,59/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,60/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,61/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,62/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,63/Itemid,11/");
			$this->loadUrl("http://www.tuffy.com/component/option,com_mtree/task,listcats/cat_id,64/Itemid,11/");
   }
	

	static function parse($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$ap = new Address_Parser();
		$oh = new Operating_Hours_Parser(); 
		$found  = false;
		
		foreach ($x->query("//a[@class='subCatNormal']") as $location)
		{
			
			$href = self::relative2absolute($url, $location->getAttribute("href"));
			self::getInstance()->loadUrl($href);
		}
		

		// parse listings now
		foreach ($x->query("///td/center/table/tr") as $node)
		{
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	
			$data = array();

			// only continue if we are on the right node
			foreach ($x2->query("//a[@class='listingName']") as $nameNode)
			{
				$data['Name'] = $nameNode->textContent;
			}

			if (isset($data['Name']) )
			{
				foreach ($x2->query("//div[@class='detailsAddress']") as $addressNode)
				{
					
					$data = array_merge($data,$ap->parse( $addressNode->c14n() ));
				}

				foreach ($x2->query("//div[@class='detailsContact']") as $contactNode)
				{
					$contactInfo = strip_tags(str_replace("<br></br>", " | ", $contactNode->c14n()));					
					$contactInfoTokens = explode("|",$contactInfo);
					foreach($contactInfoTokens as $token)
					{
						$token = trim($token);
						if (!strstr($token, "Click Here to View This Location") &&
							 !empty($token)
							)
						{
							list($key,$value) = explode(":",$token);
							$data[trim($key)] = trim($value);
						}
					}
				}

				// grab the real website location
				foreach ($x2->query("//a[contains(text(),'Click Here to View This Location')]") as $urlNode)
				{
					$trueUrl = self::getFinalAddress($urlNode->getAttribute("href"));

					$data['url'] = $trueUrl;
					$data['tuffy_bot_protection_url'] = $urlNode->getAttribute("href");
				}

				log::info($data);
				db::store($type,$data,array('Name','Telephone'),false);
			}

			
		}

			
		
	}
}
$r = new tuffys();
$r->runLoader();
$r->parseData();
$r->generateCSV();
