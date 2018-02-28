<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class sodaspeaks extends baseScrape
{
    public static $_this=null;
	
	public $description = "Serves as a network and voice for entrepreneurs and innovators around the globe who are creating the future of marketing and digital experiences.";
	public $category = "Other";
	
   public function runLoader()
   {
		$type = get_class();		
		//$this->noProxy=false;
		//$this->proxy = "localhost:8888";
		//
		$this->theads = 1;
		//db::query("UPDATE load_queue set processing = 1 where type='$type' and processing = 0 ");
	/*	
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		
			
	
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	
*/
		
		$this->loadUrl("http://sodaspeaks.com/members/");
	}

	public static function parse($url,$html)
	{
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query);
		$type = get_class();		
		$thiz = self::getInstance();
		$x = $xListings = new XPath($html);	

	
		//details
		foreach($xListings->query("//div[@class='agency_info']") as $nodeListing)
		{
			
			$x = new Xpath($nodeListing);
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();
			$ep = new Email_Parser;
			$data = array();

			foreach ($x->query("//span[contains(text(), 'About ')]") as $node)
			{
				$data['COMPANY_NAME'] = self::cleanup(str_replace("About ","", $node->textContent));
			}
			
			if (empty($data['COMPANY_NAME'] ))
				continue;
			foreach ($x->query("//div[@class='agency_about']//p[1]") as $node)
			{
				$data['ABOUT'] = self::cleanup(str_replace("About ","", $node->textContent));
			}			
			foreach ($x->query("//div[@class='agency_about']//span[contains(text(), 'Website: ')]") as $node)
			{
				$data['WEBSITE'] = self::cleanup(str_replace("Website: ","", $node->textContent));
			}
			
			foreach ($x->query("//div[@class='agency_primary_location']//div[@class='address']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
			}
			
			foreach ($x->query("//div[@class='agency_primary_location']//div[@class='contact']") as $node)
			{
				$data = array_merge($data, array_merge($ep->parse($node->textContent),$pp->parse($node->textContent)));				
			}
			
			$data["SECONDARY"] = array();
			$i="";
			foreach ($x->query("//div[@class='agency_locations']//div[@class='contact']") as $node)
			{				
				$data["SECONDARY_LOCATION ".$i++]= array_merge($ap->parse($node->textContent), array_merge($ep->parse($node->textContent),$pp->parse($node->textContent)));
				if ($i > 7 )break;
			}
			
			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;
				log::info(db::normalize($data));		
				db::store($type,$data,array('COMPANY_NAME','PHONE','ADDRESS','WEBSITE'));	
			}		
			
		}
		// did we find anything?
		
		if (!empty($data))
		{
			// next page listings
			$urls = array();
			foreach($x->query("//div[@class='pages']//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);	
			
		}
	}
}

$r= new sodaspeaks();
$r->parseCommandLine();

