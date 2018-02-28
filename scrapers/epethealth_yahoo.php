<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class epethealth_yahoo extends baseScrape
{
    public static $_this=null;
	
	function __construct()
	{
		parent::__construct();

		$this->bing = new search_engine_bing();
		$this->google = new search_engine_google();
		$this->yahoo = new search_engine_yahoo();
	}


   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		$this->maxRetries   = 50;
		$this->sleepInterval= 2; 
		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set parsed = 0 where type='epethealth_yahoo' and parsed = 1 ");

		/*db::query("UPDATE raw_data set parsed = 0 where type='epethealth_yahoo' and parsed = 1 ");
		db::query("DROP TABLE $type");
	
		
		
		
		db::query("UPDATE load_queue set processing = 0 where type='epethealth_yahoo' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='epethealth_yahoo' and parsed = 1 ");	

		
		db::query("DELETE FROM load_queue where type='epethealth_yahoo' and processing = 1 ");
		db::query("DELETE FROM raw_data where type='epethealth_yahoo' and parsed = 1 ");
*/
		$this->loadUrlsByCity($this->yahoo->url("\"epethealth\" \"%CITY%, %STATE%\""));
		$this->loadUrlsByCity($this->bing->url("\"epethealth\" \"%CITY%, %STATE%\""));

	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$host = parse_url($url,PHP_URL_HOST);

		$listings=array();
		if (preg_match("/yahoo/",$url))
			$listings = $thiz->yahoo->parse($html,true,true);
		else if(preg_match("/bing/",$url))			
			$listings = $thiz->bing->parse($html,true,true);
		else
			log::error("Unknown url $url");

		foreach($listings as $listing)
		{
			$data = array();
			$data['SITE'] = parse_url($listing['URL'],PHP_URL_HOST);
			$data['TITLE'] = $listing['TITLE'];
			$data['SOURCE_URL'] = $listing['URL'];
			$data['SEARCH_URL'] = $url;
			
			if ($data['SITE'] == 'search.yahoo.com')
				$thiz->loadUrl($listing['URL']);
			else
			{
				print_R($data);
				db::store($type,$data );
			}
		
		}
	}
}

$r= new epethealth_yahoo();
$r->parseCommandLine();

