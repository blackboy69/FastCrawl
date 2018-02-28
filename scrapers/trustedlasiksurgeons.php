<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class trustedlasiksurgeons extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
//		$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=2;
		$this->debug=false;
		
		//
/*		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			


		db::query("UPDATE raw_data set processing = 0 where type='trustedlasiksurgeons' and parsed = 1 ");
	*/
#		db::query("	DROP TABLE trustedlasiksurgeons"); 
#		db::query("UPDATE raw_data set parsed = 0 where type='trustedlasiksurgeons' and parsed = 1 ");
#		db::query("DROP TABLE $type");	
	//	$this->loadUrlsByLocation("http://www.trustedlasiksurgeons.com/lasik-doctors?zip=$ZIP&dist=100",500);
		$this->loadUrl("http://www.trustedlasiksurgeons.com/find_a_lasik_eye_surgeon.htm");

	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();

		if ($url == "http://www.trustedlasiksurgeons.com/find_a_lasik_eye_surgeon.htm")
		{
			$urls = array();
			// load listings
			foreach ($x->query("//div[@class='states-listing']//a") as $node)
			{
				
				$abs =  self::relative2absolute($url,$node->getAttribute("href"));
				
				if (strpos($url, "trustedlasiksurgeons.com") > 0)
					$urls[] = $abs;
			}
			$thiz->loadUrlsByArray($urls);
		}
		else if (strpos($url,"_lasik_surgeons.htm") > 0)
		{
			$urls = array();
			// load listings
			foreach ($x->query("//div[@class='content']//a") as $node)
			{
				
				$abs =  self::relative2absolute($url,$node->getAttribute("href"));
				
				if (strpos($url, "trustedlasiksurgeons.com") > 0)
					$urls[] = $abs;
			}
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$data = array();
			foreach ($x->query("//h1[@class='title']") as $node)
			{
				@list($junk,$data['NAME']) = explode("LASIK", $node->textContent);
			}
			
			if (! isset($data['NAME']))
				return;

			foreach ($x->query("//div[@class='field-items']//p") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));

				if (isset($data['ZIP']) && isset($data['CITY']) && isset($data['STATE'])  && isset($data['ADDRESS']))
					break;
			}

			if (!(isset($data['ZIP']) && isset($data['CITY']) && isset($data['STATE'])  && isset($data['ADDRESS'])))
				return;

			foreach ($x->query("//div[@class='field-items']") as $node)
			{
				$data = array_merge($data,$pp->parse($node->c14n()));
			}
			
			foreach ($x->query("//div[@class='field-items']") as $node)
			{
				$data = array_merge($data,$ep->parse($node->c14n()));
			}

			foreach ($x->query("//div[@class='field-items']//a") as $node)
			{
				$data['WEB_SITE'] =  $node->getAttribute("href");
				break;
			}
			
#			unset ($data['RAW_ADDRESS']);
		
			if (!empty($data['NAME']) && isset($data['ZIP']) && isset($data['CITY']) && isset($data['STATE'])  && isset($data['ADDRESS']))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('NAME','PHONE','ZIP'));	
			}
		
		}

	

	}
}

$r= new trustedlasiksurgeons();
$r->parseCommandLine();

