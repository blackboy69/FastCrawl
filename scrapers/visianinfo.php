<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class visianinfo extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
//		$this->proxy = "localhost:8888";
		$this->maxRetries = 1;
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=2;
		$this->debug=false;
		
		//
/*		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			


		db::query("UPDATE raw_data set processing = 0 where type='visianinfo' and parsed = 1 ");

#		db::query("	DROP TABLE visianinfo"); 
		db::query("UPDATE raw_data set parsed = 0 where type='visianinfo' and parsed = 1 ");
		db::query("DROP TABLE $type");	
	*/		
		$urls = $this->loadUrlsByLocation("http://visianinfo.com/wp-content/themes/visian/surgeon-lookup-xml.php?lat=%LAT%&lng=%LON%&radius=50&do_not_expand=0");
	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$xListing = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();

		

		foreach($xListing->query("//marker") as $nodeListing)
		{
			$data = array();

			foreach($nodeListing->attributes as $attr)
			{
				$data[$attr->name] = $attr->value;
			}

			if (isset($data['address']))
			{
				$data = array_merge($data,$ap->parse($data['address']));
				$data['SOURCE_URL'] = $url;

				$data = db::normalize($data);		
				log::info($data);		
				db::store($type,$data,array('ADDRESS','ZIP', 'NAME'));	
			}
	}
	}
}

$r= new visianinfo();
$r->parseCommandLine();

