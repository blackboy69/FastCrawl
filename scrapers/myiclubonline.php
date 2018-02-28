<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class myiclubonline extends baseScrape
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
			 url IN (SELECT url FROM raw_data WHERE type ='myiclubonline' and LENGTH(html) < 3000)
			 AND type ='myiclubonline'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='myiclubonline'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='myiclubonline')
				  AND processing = 0
			     AND type ='myiclubonline'
		    )
		
			 ");
		
		*/
		// cananda top 100 cities by population
		//db::query("UPDATE raw_data set parsed = 0 where type='myiclubonline' and parsed = 1  ");
		//db::query("drop table $type");
		
	$this->maxRetries=1;
	//	$this->noProxy=true;
//		$this->switchProxy(null,true);

		$urls = array();
		for($i=1;$i<=25000;$i++)
		{
			// do it manually becuase need to be lowercase.
			$urls[] = "https://mico.myiclubonline.com/iclub/club/getClub.htm?club=".sprintf("%04d",$i);
		}

		if (!empty($urls))
			$this->loadUrlsByArray($urls);	
	}


	public static function parse($url,$html)
	{
		$type = get_class();		

		$json_data = json_decode($html,true);
		$data = db::normalize($json_data[0]);
		$data['XID'] = $data['ID'];
		
		unset ($data['ID']);

		$data['SOURCE_URL'] = $url;
		log::info($data);					
		
			db::store($type,$data,array('SOURCE_URL'));	

	}
	
}

$r= new myiclubonline();
$r->parseCommandLine();

