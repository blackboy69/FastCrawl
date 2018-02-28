<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class athenahealth extends baseScrape
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
		$this->threads=8;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='athenahealth' and LENGTH(html) < 3000)
			 AND type ='athenahealth'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='athenahealth'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='athenahealth')
				  AND processing = 0
			     AND type ='athenahealth'
		    )
		
			 ");
		
		*/
		// cananda top 100 cities by population
		//db::query("UPDATE raw_data set parsed = 0 where type='athenahealth' and parsed = 1  ");
		
	//	$this->noProxy=true;
//		$this->switchProxy(null,true);

		$urls = array();
		for($i=0;$i<10000;$i++)
		{
			// do it manually becuase need to be lowercase.
			$urls[] = "https://$i.portal.athenahealth.com/";
		}
		if (!empty($urls))
			$this->loadUrlsByArray($urls);	
	}

	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		$host = parse_url($url,PHP_URL_HOST);

		/*
		if (strpos($html,"Sorry, you're not allowed to access this page."))
		{			
			log::info("Sorry, you're not allowed to access this page.");					
			$html=null;
		}

		if (strlen($html)<1000) // just skip
		{$html=null;}*/

		if (empty($html))
		{
			$html = "                                          ,,,,,..04BCFGJNNOOTaaaaaaaaaaaaaaaaaaabcccccccccccdddddddddddeeeeeeeeeeeeeeeeeeeeeeeeeeffffggghhhhhhhhhhhiiiiiiiiiiiiiiiiiiijklllllllmmmnnnnnnnnnnnnnnnoooooooooooooooooooooppppppppppprrrrrrrrrrrrrrrrrrrrrrrrrsssssssssssssttttttttttttttttttuuuuvvwwwyyyy’";
		}
		baseScrape::loadCallBack($url,$html,$arg3);
		//sleep(1);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	

		$pp = new Phone_Parser();
		$data = array();

		
		foreach ($x->query("//h1[contains(text(),'Welcome to')]") as $node)
		{
			$title = self::cleanup($node->textContent);
			$title = str_replace("Welcome to the","",$title);
			$title = str_replace("Welcome to","",$title);
			$title = str_replace("Patient Portal!","",$title);
			$data['COMPANY'] =self::cleanup($title);
		}

		foreach ($x->query("//div[@id='contentDiv']") as $node)
		{
			$data = array_merge($data,$pp->parse($node->textContent));
		}
		
		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;
			log::info($data);					
			db::store($type,$data,array('SOURCE_URL'));	
		}					
	}
	
}

$r= new athenahealth();
$r->parseCommandLine();

