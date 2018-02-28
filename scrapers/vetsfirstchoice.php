<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class vetsfirstchoice extends baseScrape
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
			 url IN (SELECT url FROM raw_data WHERE type ='vetsfirstchoice' and LENGTH(html) < 3000)
			 AND type ='vetsfirstchoice'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='vetsfirstchoice'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='vetsfirstchoice')
				  AND processing = 0
			     AND type ='vetsfirstchoice'
		    )
		
			 ");
		
		*/
		// cananda top 100 cities by population
		db::query("UPDATE raw_data set parsed = 0 where type='vetsfirstchoice' and parsed = 1  ");
		db::query("drop table $type");
		
	//	$this->noProxy=true;
//		$this->switchProxy(null,true);

// 
// https://store.vetsfirstchoice.com/practice/findVet_ajax/find/addresses/[%7B%22lat%22:%2038.440429,%20%22lng%22:%20-122.71405479999999%7D]?_search=false&nd=1416423708025&rows=20&page=1&sidx=&sord=asc
		

		$url = "https://store.vetsfirstchoice.com/practice/findVet_ajax/find/addresses/";
		$url .= '[{"lat":%LAT%,"lng":%LON%}]';
		$url .= "?_search=false&nd=1416423708025&rows=2000&page=1&sidx=&sord=asc";
		$this->loadUrlsByLocation($url,'',2500);
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$x = new  XPath($html);	
		$json_data = json_decode($html,true);		

		foreach ($json_data['rows'] as $key => $row)
		{
			$data = array();
			$data['COMPANY'] = @$row['cell'][0];
			$data['CITY']  = @$row['cell'][1];
			$data['STATE']  = @$row['cell'][2];
			$data['ZIP']  = @$row['cell'][3];
			$data['PHONE']  = @$row['cell'][4];
	
			$data['SOURCE_URL'] = $url;
			log::info($data);					
			db::store($type,$data,array('COMPANY','PHONE'),$overwrite=true);	
		}		
	
	}
	
}

$r= new vetsfirstchoice();
$r->parseCommandLine();

