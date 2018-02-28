<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class freshbooks extends baseScrape
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
			 url IN (SELECT url FROM raw_data WHERE type ='freshbooks' and LENGTH(html) < 3000)
			 AND type ='freshbooks'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='freshbooks'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='freshbooks')
				  AND processing = 0
			     AND type ='freshbooks'
		    )
		
			 ");
		
		*/
		// cananda top 100 cities by population
		db::query("UPDATE raw_data set parsed = 0 where type='freshbooks' and parsed = 1  ");
		db::query("drop table $type");
		
//	$this->maxRetries=1;
	//	$this->noProxy=true;
//		$this->switchProxy(null,true);

		$this->loadUrl("http://www.freshbooks.com/ext/freshmap/accountant_data.js.php",true);
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		
		$json = preg_replace("/^var accountants = /","", $html);
		$json = preg_replace("/;$/","", $json);
		$allData = json_decode($json,true);
		
		foreach($allData as $d)
		{
			$data =  db::normalize($d);

			$data['XID'] = $data['ID'];			
			unset ($data['ID']);
			$data['SOURCE_URL'] = $url;

			log::info($data);					
			db::store($type,$data,array('XID'));	
		}

	}
	
}

$r= new freshbooks();
$r->parseCommandLine();

