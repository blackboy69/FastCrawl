<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class surecritic extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		//$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=8;
		$this->debug=false;
		
		//
	/*	
		
		
			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			

		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='surecritic' and parsed = 1 ");


		
	*/
			db::query("	DROP TABLE surecritic"); 
	db::query("UPDATE raw_data set parsed = 0 where type='surecritic' and parsed = 1 ");
		
		$this->loadUrlsByLocation("http://www.surecritic.com/reviews?commit=search&near=%ZIP%&page=&term=&utf8=");

		//db::query("UPDATE load_queue set processing = 0 where type='surecritic' and processing = 1 ");
		//db::query("UPDATE raw_data set parsed = 0 where type='surecritic' and parsed = 1 ");
		//db::query("DROP TABLE $type");	
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();

		parse_str(parse_url($url,PHP_URL_QUERY),$query);

		if (!empty($query['near']))
		{
			$urls = array();
			// load listings
			foreach ($x->query("//div[@class='business-info']//a[contains(@name,'business')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			foreach ($x->query("//div[@class='pagination']//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$data = array();
			foreach ($x->query("//h1[contains(@itemprop,'name')]") as $node)
			{
				$data['NAME'] =$node->textContent;
			}

			foreach ($x->query("//div[@class='address']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
			}
			
			foreach ($x->query("//div[@class='phone']") as $node)
			{
				$data['PHONE'] =$node->textContent;
			}

			foreach ($x->query("//div[@class='website']//a") as $node)
			{
				$data['WEBSITE'] =$node->getAttribute("href");
			}

			foreach ($x->query("//span[@itemprop='count']") as $node)
			{
				$data['NUM_REVIEWS'] =$node->textContent;
			}
			
			if (!empty($data['NAME']))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('NAME','PHONE','ZIP'));	
			}
		
		}

	

	}
}

$r= new surecritic();
$r->parseCommandLine();

