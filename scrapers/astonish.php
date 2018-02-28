<?
include_once "config.inc";
include_once "search_engine_yahoo.php";


class astonish extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		$this->maxRetries = 2;
		$this->timeout = 5;
		//$this->useCookies=false;
	$this->noProxy=true;
//		$this->proxy='127.0.0.1:8888';
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=5;
		$this->useCookies=true;

///		db::query("DELETE FROM raw_data where type='astonish'   ");
	//	db::query("DELETE FROM load_queue where type='astonish'   ");
	//	db::query("DROP TABLE astonish");
//	db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		
		$urls = file("astonish.txt");
		$this->userAgent = "";
		$this->loadUrlsByArray($urls);
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
		}

		#Name
		foreach ($x->query("//*[@itemprop='name']") as $node)
		{

			$data['NAME'] =  $node->textContent;
		}

		
		
		foreach ($x->query("//*[@class='address']") as $node)
		{
			#address
			$data = array_merge($data,$ap->parse($node->textContent));			
			if (!empty($data['ZIP']))
				break;
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

$r= new astonish();
$r->parseCommandLine();

