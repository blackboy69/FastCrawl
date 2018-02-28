<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class brightfire extends baseScrape
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
		$this->threads=4;

		//db::query("DELETE FROM raw_data where type='brightfire'   ");
		//db::query("DELETE FROM load_queue where type='brightfire'   ");
		db::query("DROP TABLE brightfire");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		
		$urls = file("brightfire.txt");
		//	$urls = array;
		$this->loadUrlsByArray($urls);

	}


	public static function parse($url,$html,$break=false)
	{
		log::info("In parse $url");
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		$ep = new Email_Parser();
		
		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query);

		if (empty($query['contact']))
		{		
			$urls = array();
			foreach ($x->query("//a[contains(translate(text(), 'CONTACT US', 'contact us'),'contact us')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"))."?contact=true";
			}
			$thiz->loadUrlsByArray($urls);
			return;
		}
		$data = array();
		
		#Name
		foreach ($x->query("//div[@id='header']//img") as $node)
		{
			@list($data['NAME'], $junk) = explode("-", $node->getAttribute("alt"));
		}
		
		foreach ($x->query("//p") as $node)
		{
			$data = array_merge($data,$ap->parse($node->c14n()));

			if (isset($data['ZIP']) && isset($data['CITY']) && isset($data['STATE'])  && isset($data['ADDRESS']))
				break;
		}

		if (! (isset($data['ZIP']) && isset($data['ADDRESS'])) )
				return;

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

$r= new brightfire();
$r->parseCommandLine();

