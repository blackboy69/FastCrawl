<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class smallbusinessbiggame extends baseScrape
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
			 url IN (SELECT url FROM raw_data WHERE type ='smallbusinessbiggame' and LENGTH(html) < 3000)
			 AND type ='smallbusinessbiggame'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='smallbusinessbiggame'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='smallbusinessbiggame')
				  AND processing = 0
			     AND type ='smallbusinessbiggame'
		    )
		
			 ");
		
		*/
		// cananda top 100 cities by population
		db::query("UPDATE raw_data set parsed = 0 where type='smallbusinessbiggame' and parsed = 1  ");
		db::query("drop table $type");
		
	//	$this->noProxy=true;
//		$this->switchProxy(null,true);

		$urls = array();
		for($i=0;$i<=88;$i++)
		{
			// do it manually becuase need to be lowercase.
			$urls[] = "https://www.smallbusinessbiggame.com/v4/api/gallery/get.json?environment=1&category_id=$i&sort=1&start=1&total=100";
		}
		if (!empty($urls))
			$this->loadUrlsByArray($urls);	
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	
		$pp = new Phone_Parser();


		$json_data = json_decode($html,true);
		
		if (!empty($json_data['paging']['next']))
		{
			$thiz->loadUrl($json_data['paging']['next']);
		}
		foreach ($json_data['results'] as $json)
		{
			$data = array();
			if (!isset($json['business_name']))
			{
				log::info("missing name");
				//print_R($json);
				continue;
			}
			$data['COMPANY'] = @$json['business_name'];
			$data['CITY']  = @$json['City'];
			$data['STATE']  = @$json['State'];
			$data['WEBSITE']  = @$json['business_url'];
			$data['DESCRIPTION']  = @$json['description'];
			$data['CATEGORIES']  = @$json['categories'][0]['category_name'];

#			$data = array_merge($data,$json);



			if (!empty($data['CITY']) && !empty($data['COMPANY'])	 )
			{
				$yl = new search_engine_yahoo_local();
				$yahoo_data = $yl->parse($thiz->get($yl->url($data['COMPANY'],"{$data['CITY']}, {$data['STATE']}")));

				foreach($yahoo_data as $ydata)
				{
					if (strtolower(@$ydata['Title']) ==  strtolower($data['COMPANY']) ||
  						 strtolower(@$ydata['BusinessUrl']) ==  strtolower(@$data['WEBSITE'])
					)
					{
						$data = array_merge($data,$ydata);
					}
				}
				$data['SOURCE_URL'] = $url;
				log::info($data);					
				db::store($type,$data,array('COMPANY','CITY','STATE'),$overwrite=true);	
			}		
		}
	}
	
}

$r= new smallbusinessbiggame();
$r->parseCommandLine();

