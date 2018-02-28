<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class goodyear extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		$this->proxy = "localhost:8888";

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

		$sql = "SELECT zip FROM geo.locations where pop > 75000";

		Log::info("Loading....");

		foreach(db::OneCol($sql) as $i => $zip)
		{
			echo ".";
			$toPost="header-search=$zip&retailers-filters=false&features-filter=";
			$reqs[] = new WebRequest("www.goodyear.com/en-US/tires/tire-shop/all/1/100/post/retailers-result?$zip",$type,"POST",$toPost);
		} 
		$this->loadWebRequests($reqs);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	

		$ap = new Address_Parser();
		$op = new Operating_Hours_Parser();
		$pp = new Phone_Parser();


		if (preg_match("/\?/",$url))
		{
			/// get next page links
			list($junk, $zip) = explode("?", $url);
			$reqs = array();
			foreach($x->query("//ul[@class='navigation']//a//var") as $node)
			{
				$page = $node->textContent;
				$toPost="header-search=$zip&retailers-filters=false&features-filter=";
				$reqs[] = new WebRequest("www.goodyear.com/en-US/tires/tire-shop/all/$page/100/post/retailers-result?$zip",$type,"POST",$toPost);
			}
			if (!empty($reqs))
				$thiz->loadWebRequests($reqs);				

			foreach ($x->query("//li[contains(@class,'retailer-details')]") as $node)
			{
				$data = array();

				$x2 = new Xpath($node);

				foreach($x2->query("//h4/a") as $node2)
				{
					$data['NAME'] = $node2->textContent;
				}

				// grab address
				$address = array();	
				foreach($x2->query("//ul[@class='address-results']/li") as $node2)
				{
					if ($node2->textContent == "Address:")
						continue;
					else if (preg_match("/Distance [0-9]+/", $node2->textContent))
						continue;
					else if (preg_match("/Website/", $node2->textContent))
						$data['WEBSITE']  = $node2->getAttribute("href");			
					else
						$address[] = $node2->textContent;
				}
				$data = array_merge($data,$ap->parse($address));
				$data = array_merge($data,$pp->parse($address));
				
				// grab hours
				$hours=array();
				foreach($x2->query("//ul[@class='hours-results']/li") as $node2)
				{
					if ($node2->textContent == "Hours:")
						continue;
					else
						$hours[] = $node2->textContent;
				}
				$data = array_merge($data,$op->parse($hours));

				//branding
				foreach($x2->query("//div[@class='branding-container']/img") as $node2)
				{
					if ($node2->textContent == "Hours:")
						continue;
					else
						$data[$node2->getAttribute("alt")] =  1;
				}
				
				// services offered
				$services=array();
				foreach($x2->query("//ul[@class='services-results']/li") as $node2)
				{
					if ($node2->textContent == "Store Details:")
						continue;
					else
						$data["STORE_DETAILS_".$node2->textContent] =  1;
					$services[] = $node2->textContent;
				}
				$data['RAW_STORE_DETAILS'] = join(",",$services);
			
				$data = db::normalize($data);
				if (!empty($data))
				{
					log::info($data);		
					db::store($type,$data,array('NAME','PHONE'));	
				}
			
				else 
				{
					log::error("Unknown url $url");
				}
			}
		}
	}
}

$r= new goodyear();
$r->parseCommandLine();

