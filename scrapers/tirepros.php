<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class tirepros extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		//$this->noProxy=false;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
	/*	
		
		
			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			

		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='tirepros' and parsed = 1 ");

		db::query("	DROP TABLE tirepros"); 
		db::query("UPDATE raw_data set parsed = 0 where type='tirepros' and parsed = 1 ");
	*/

		$this->loadUrlsByState( "http://www.tirepros.com/dealer-locator?province=%STATE%&city=&distance%5Bpostal_code%5D=&distance%5Bsearch_distance%5D=&distance%5Bsearch_units%5D=mile");

		//db::query("UPDATE load_queue set processing = 0 where type='tirepros' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='tirepros' and parsed = 1 ");
		db::query("DROP TABLE $type");	
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$hp=new Operating_Hours_Parser();
		$kvp = new KeyValue_Parser();


		// parse listings
		if (preg_match("/tirepros\.com\/dealer-locator/",$url))
		{
			$urls = array();
			// load listings
			foreach ($x->query("//a[@class='dealer_name']") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			foreach ($x->query("//ul[@class='pager']//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}


			$thiz->loadUrlsByArray($urls);
		}
		// parse details
		else
		{
			log::info("WTF?");

			$data = array();
			foreach ($x->query("//div[contains(@class,'page_title')]") as $node)
			{
				$data['NAME'] =$node->textContent;
			}

			foreach ($x->query("//div[@class='tel']") as $node)
			{
				$data['PHONE'] =$node->textContent;
			}			

			foreach ($x->query("//div[@class='address']") as $node)
			{
				$data = array_merge($data, $ap->parse($node->c14n()));
			}

			foreach ($x->query("//div[@class='dealer_hours']") as $node)
			{
				$data = array_merge($data, $hp->parse($node->textContent));
			}
			
			// Dealer information (years in business...) 
			foreach ($x->query("//div[@class='entry']") as $node)
			{
				$key = strtoupper(trim($node->firstChild->textContent));
				$value = $node->firstChild->nextSibling->textContent;
				$data[$key] = $value;
			}
									
			$images = array();
			foreach ($x->query("//div[@class='photo_gallery']//img") as $node)
			{
				$images[] = self::relative2absolute($node->textContent,$url);
			}
			$data['STORE_IMAGES'] = join(" , ",$images);

			if (!empty($data['NAME']))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('NAME','PHONE','ZIP'));	
			}
		
		}

	

	}
}

$r= new tirepros();
$r->parseCommandLine();

