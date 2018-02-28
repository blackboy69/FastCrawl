<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class beyondindigopets extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

//		$this->maxRetries = 100;
	//	$this->timeout = 15;
		//$this->useCookies=false;
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=2;

		db::query("UPDATE raw_data set parsed = 0 where type='beyondindigopets' and parsed = 1  ");
		db::query("DROP TABLE beyondindigopets");
		
		$this->loadUrl("http://www.beyondindigopets.com/our-customers/");
	}


	public static function parse($url,$html,$break=false)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

		if (preg_match("/beyondindigopets/", $url))
		{
			// get links to listing pages links
			$urls = array();
			foreach($x->query("//div[@id='bodytext']//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			if (!empty($urls))
				$thiz->loadUrlsByArray($urls);	
			return;
		}
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		$ep = new Email_Parser();

		$data = array();
		
		#Name
		foreach ($x->query("//title") as $node)
		{
			$data['PAGE_TITLE'] = self::cleanup($node->textContent);
		}
		
		#address
		$data = array_merge($data,$ap->parse($html));
		unset($data['RAW_ADDRESS']);
		
		#Phone numbers
		$data = array_merge($data,$pp->parse($html));

		#Email Address
		$data = array_merge($data,$ep->parse($html));


		if ( isset($data[""]) )
		{
			$data['UNKNOWN'] = $data[""];
			unset($data[""]);
		}
		
		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;
			unset ($data['RAW_ADDRESS']);

			log::info($data);
			db::store($type,$data,array('SOURCE_URL'));	
			return true;
		}		
		
	}
}

$r= new beyondindigopets();
$r->parseCommandLine();

