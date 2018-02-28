<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class carstar extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
	//	$this->noProxy=false;
//		$this->proxy = "localhost:8888";
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
		/*
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		
	db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
		*/
		//db::query("Drop table $type");

		// db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");

		$states = array ('Alabama','Alaska','Alberta','Arizona','Arkansas','British Columbia','California','Colorado','Connecticut','D. Columbia','Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Manitoba','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Brunswick','New Hampshire','New Jersey','New Mexico','New York','Newfoundland & Labrador','North Carolina','North Dakota','Northwest Territories','Nova Scotia','Nunavut','Ohio','Oklahoma','Ontario','Oregon','Pennsylvania','Prince Edward Island','Puerto Rico','Quebec','Rhode Island','Saskatchewan','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming','Yukon Territory');
		
		foreach ($states as $state)
		{
			$urls[] = "http://www.carstar.com/Portals/0/StoreFinderResults.aspx?state=".urlencode($state);
		}
		$this->loadUrlsByArray($urls);
		//$this->loadUrl("http://www.carstar.com/Portals/0/StoreFinderResults.aspx?state=Wisconsin");
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$ap = new Address_Parser();
		$kvp = new KeyValue_Parser();
		if (strpos($url, "StoreFinderResults") !== false)
		{
			$urls = array();
			foreach($x->query("//a[contains(@href,'carstar.com')]") as $node)
			{
				$href = self::relative2absolute($url,$node->getAttribute("href"));
				$urls[] = $href;//$node->getAttribute("href");

			}
			$thiz->loadUrlsByArray($urls);
		
		}
		else
		{	
			$data = array();
			$side = array();
			foreach($x->query("//table//tr[3]//p[@class='side']") as $node)
			{
				$x2 = new XPath($node);
				$preside=$side = array_merge($side, explode("|BREAK|", strip_tags(str_replace("<br>","|BREAK|", $x2->html))));
			}
			$data = array_merge($data,$kvp->parse($side));
			$data = db::normalize($data);

			if(isset($side[5]))
			{
				if (isset($data['EMAIL']) )
				{
					$side= array_slice($side,2);
				}
				$data['COMPANY_NAME'] = str_replace("&amp;","&", $side[0]);
				$data['ADDRESS'] = $side[1];
				$data['CITY'] = $side[2];
				$data['STATE'] = $side[3];
				$data['ZIP'] = $side[5];
				$data['CAPTION'] = " ";
				$data['STORE_PICTURE'] ="";
				$data['SOURCEURL'] = $url;
			}

			
			foreach($x->query("//table//tr[3]//p[@align='center']") as $node)
			{
				$x2 = new XPath($node);
				foreach($x2->query("//b") as $node2)
				{
					$data['CAPTION'] .= "$node2->textContent. ";
				}

				foreach($x2->query("//img") as $node2)
				{
					$data['STORE_PICTURE'] = self::relative2absolute($url,$node2->getAttribute("src"));
				}
			}

			
			if (!empty($data))
			{
				log::info($data);		
				db::store($type,$data,array('SOURCEURL'));	
			}
		}
	}
}

$r= new carstar();
$r->parseCommandLine();

