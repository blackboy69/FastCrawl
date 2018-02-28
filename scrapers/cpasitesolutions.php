<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class cpasitesolutions extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

//		$this->maxRetries = 100;
	//	$this->timeout = 15;
		//$this->useCookies=false;
		$this->noProxy=false;
		$this->proxy='127.0.0.1:9996';
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=2;

		//db::query("UPDATE raw_data set parsed = 0 where type='cpasitesolutions' and parsed = 1  ");
		//db::query("DROP TABLE cpasitesolutions");
		
		$urls = file("cpasitesolutions.txt");
		$this->loadUrlsByArray($urls);
	}


	public static function parse($url,$html,$break=false)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

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
		
		
		#Phone numbers
		$data = array_merge($data,$pp->parse($html));

		#Email Address
		$data = array_merge($data,$ep->parse($html));

		
		if ( isset($data[""]) )
		{
			$data['UNKNOWN'] = $data[""];
			unset($data[""]);
		}
		
		$data['WEBSITE'] = dirname($url);

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

$r= new cpasitesolutions();
$r->parseCommandLine();

