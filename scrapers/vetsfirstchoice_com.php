<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class vetsfirstchoice_com extends baseScrape
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
		$this->threads=1;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='vetsfirstchoice_com' and LENGTH(html) < 3000)
			 AND type ='vetsfirstchoice_com'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='vetsfirstchoice_com'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='vetsfirstchoice_com')
				  AND processing = 0
			     AND type ='vetsfirstchoice_com'
		    )
		
			 ");
		
		
		// cananda top 100 cities by population
		db::query("UPDATE raw_data set parsed = 0 where type='vetsfirstchoice_com' and parsed = 1  ");
		db::query("drop table $type");
		*/
	//	$this->noProxy=true;
//		$this->switchProxy(null,true);

// 
// https://store.vetsfirstchoice.com/practice/findVet_ajax/find/addresses/[%7B%22lat%22:%2038.440429,%20%22lng%22:%20-122.71405479999999%7D]?_search=false&nd=1416423708025&rows=20&page=1&sidx=&sord=asc
		/*
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		*/
		$url = "https://store.vetsfirstchoice.com/practice/findVet_ajax/find?latitude=%LAT%&longitude=%LON%&page=1";
		$this->loadUrlsByLocation($url,'',2500);
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$x = new  XPath($html);	
		$json_data = json_decode($html,true);		

		if ($json_data["current_page"] < $json_data["total_pages"])
		{
			$thiz=self::getInstance();
			$page = $json_data['current_page']+1;
			$urlToLoad = preg_replace("#&page=([0-9]+)#", "&page=$page", $url);
			$thiz->loadUrl($urlToLoad);
		}
		foreach ($json_data['practices'] as $data)
		{
			$data["xid"] = $data["id"];
			unset($data["id"]);
	
			$data['SOURCE_URL'] = $url;
			log::info($data);					
			db::store($type,$data,array('XID'),$overwrite=true);	
		}		
	
	}
	
}

$r= new vetsfirstchoice_com();
$r->parseCommandLine();

