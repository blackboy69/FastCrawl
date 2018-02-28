<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class ascnet extends baseScrape
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


		db::query("UPDATE raw_data set processing = 0 where type='ascnet' and parsed = 1 ");
	*/
#		db::query("	DROP TABLE ascnet"); 
		db::query("UPDATE raw_data set parsed = 0 where type='ascnet' and parsed = 1 ");
		db::query("DROP TABLE $type");	
		
		$urls = file("$type.txt");
		$this->loadUrlsByArray($urls);
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

		
		$data = array();
		foreach($xListing->query("//*[@id='container']//tr//br/following-sibling::node()") as $nodeListing)
		{

//log::info($nodeListing->textContent);
			$x = new XPath($nodeListing);	

			foreach ($x->query("//a") as $node)
			{
				$data = array();

				if (strpos($node->textContent,",")>0)
					list($data['NAME'],$data['TITLE']) =  explode(",", trim($node->textContent));
				else 
					$data['NAME'] = trim($node->textContent);

				$data['EMAIL'] =  trim(str_replace("mailto:", "", $node->getattribute("href")));
				continue;
			}
			if (stripos( $nodeListing->c14n(), "C/O ") ==1)
				$data['BUISNESS_NAME'] = str_replace("C/O ", ""	, trim($nodeListing->textContent));

			$data = array_merge($data,$ap->parse($nodeListing->c14n()));
			$data = array_merge($data,$pp->parse($nodeListing->c14n()));
			$data = array_merge($data,$ep->parse($nodeListing->c14n()));
			
			
			unset($data['COUNTRY']);
/*
			foreach ($x->query("//a") as $node)
			{
				$data['WEB_SITE'] =  $node->getAttribute("href");
			}*/
		
			if (!empty($data['ZIP']) )
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('SOURCE_URL', 'NAME'));	
			}
		}
	}
}

$r= new ascnet();
$r->parseCommandLine();

