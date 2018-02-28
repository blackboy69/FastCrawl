<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class alicorsoultions extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		$this->maxRetries = 3;
		$this->timeout = 5;
		//$this->useCookies=false;
		$this->noProxy=true;
	//	$this->proxy='127.0.0.1:9996';
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=8;

		//db::query("DELETE FROM raw_data where type='alicorsoultions'   ");
		//db::query("DELETE FROM load_queue where type='alicorsoultions'   ");
db::query("DROP TABLE alicorsoultions");
db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		
		$urls = file("alicorsolutions.txt");
//	$urls = array;
	$this->loadUrlsByArray($urls);
	$this->loadUrl("http://welbornins.com/contact.html");
	}


	public static function parse($url,$html,$break=false)
	{
		log::info("In parse");
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
			if (preg_match("/Contact Information for (.+) in/",$node->textContent,$matches))
				$data['NAME'] = $matches[1];
	
		}
		
		
		foreach ($x->query("//div") as $node)
		{
			#address
			$data = array_merge($data,$ap->parse($node->textContent));
			
			
		}
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
//			$data['RAW_ADDRESS'];

			log::info($data);
			db::store($type,$data,array('SOURCE_URL'));	
			return true;
		}		
		
	}
}

$r= new alicorsoultions();
$r->parseCommandLine();

