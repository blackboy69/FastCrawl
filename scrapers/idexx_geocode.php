<?php

include_once "config.inc";
include_once "search_engine_yahoo_local_scraper.php";


class idexx_geocode extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		$type = get_class($this);		
		$this->noProxy=false;
		//$this->switchProxy();
		$this->allowRedirects = false;
		$this->threads=4;
		$this->debug=false;
		//log::error_level(ERROR_ALL);
		$this->cookies=false;
		
		$ap = new Address_Parser();
		
		$sql = "select * from demandforce_vet.idexx  where id not in (select distinct(idexx_id) from $type)";

		$results = db::query($sql,true);
		
		// now load the urls for yahoo placefinder...
		$y = new search_engine_yahoo_local();
		$urls = array();
		foreach($results as $i => $data)
		{
			$state = isset($data['STATE']) ? ",". $data['STATE'] : "";
			$yahooUrl = $y->url($data['BUSINESS_NAME'],$data['CITY'].$state);
			
			$id = $data['id'];
			$urls[] = $yahooUrl."&idexx_id=$id";
		}
		db::query("DELETE FROM load_queue where processing = 0 and type = '$type'");
		$this->loadUrlsByArray($urls);	
		$this->queuedFetch();
	}

	static function loadCallBack($url,$html)
	{
	
		if (strlen($html) < 10000 )
		{
			$html = null;
		}	
		baseScrape::loadCallBack($url,$html);
	
	}

	public static function parse($url,$html)
	{
		$query = array();

		$thiz = self::getInstance();		
		$type = get_class($thiz);		
		$host = parse_url($url,PHP_URL_HOST);

		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$x = new Xpath($html);
		echo ".";
		if (preg_match("/yahoo/",$url))
		{
			// now load the urls for yahoo placefinder...
			$y = new search_engine_yahoo_local();
			$datas = $y->parse($html);

			if (empty($datas))
			{
				echo "0";
				return;
			}

			foreach($datas as $data)
			{
				if (isset($data['TITLE']))
				{
					$data['IDEXX_ID']=$query['idexx_id'];
					$data['URL'] = $url;
					//log::info($url);
					//log::info($data);
					echo "+";
					db::store($type,$data,array('TITLE','IDEXX_ID'));
				}
			}
		}
	}

	public function csvSql()
	{
		return array(get_class($this) => "
			SELECT 
				levenshtein_ratio(i.BUSINESS_NAME, ig.TITLE)	as NAME_MATCH_PERCENT,
				levenshtein_ratio(i.CITY, ig.CITY)				as CITY_MATCH_PERCENT,
				levenshtein_ratio(i.CITY, ig.STATE)				as STATE_MATCH_PERCENT,
				i.PREFIX as NAME_PREFIX,
				i.FIRST_NAME,
				i.TITLE as JOB_TITLE,
				i.BUSINESS_NAME,
				i.CITY,
				i.STATE,
				ig.TITLE		as GEOCODE_TITLE,
				ig.STATE		 as GEOCODE_STATE,
				ig.ZIP		 as GEOCODE_ZIP,
				ig.CITY		 as GEOCODE_CITY,
				ig.ADDRESS	 as GEOCODE_ADDRESS,
				ig.TELEPHONE as GEOCODE_TELEPHONE,
				ig.WEBSITE	 as GEOCODE_WEBSITE,
				ig.REVIEWS	 as GEOCODE_REVIEWS,
				ig.RATING	 as GEOCODE_RATING,
				ig.COUNTRY	 as GEOCODE_COUNTRY,
				ig.RAW_ADDRESS	as GEOCODE_RAW_ADDRESS,
				i.SOURCEURL  as IDEXX_SOURCEURL,
				ig.URL		 as GEOCODE_SOURCEURL,
				ig.IDEXX_ID

			FROM demandforce_vet.idexx i
				INNER JOIN idexx_geocode ig ON ig.idexx_id = i.id 
		");
	}
}


$r= new idexx_geocode();

 $r->parseCommandLine();



