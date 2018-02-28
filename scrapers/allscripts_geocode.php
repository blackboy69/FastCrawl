<?php

include_once "config.inc";
include_once "search_engine_yahoo_local_scraper.php";


class allscripts_geocode extends baseScrape
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
		
		$sql = "select * from allscripts where id not in (select distinct(allscripts_id) from allscripts)";

		$results = db::query($sql,true);
		
		// now load the urls for yahoo placefinder...
		$y = new search_engine_yahoo_local();
		$urls = array();
		foreach($results as $i => $data)
		{/*
		 id
 NAME
 STATUS_LEVEL
 ORGANIZATION
 SITE
 PHONE_NUMBER
 MEMBER_SINCE
 LAST_LOGGED_IN
 URL
 EMAIL
 WEB_SITE
 EXT*/
			$yahooUrl = $y->url($data['ORGANIZATION'],$data['ORGANIZATION']);
			
			$id = $data['id'];
			$urls[] = $yahooUrl."&allscripts_id=$id";
		}
		// this is nessecary becaues there is a different url for each session.,
/*
		db::query("DELETE FROM load_queue where processing = 0 and type = '$type'");
		$this->loadUrlsByArray($urls);	
*/
		$this->queuedFetch();
	}
/*
	static function loadCallBack($url,$html)
	{
	
		if (strlen($html) > 9000 )
		{
			//$html = null;
			baseScrape::loadCallBack($url,$html);
		}	

	
	}*/

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
				// echo "0";
				return;
			}
			
			$toInsert = array();
			foreach($datas as $i => $data)
			{
				if (isset($data['TITLE']))
				{
					$data['ALLSCRIPTS_ID']=$query['allscripts_id'];
					$data['URL'] = $url;
					//log::info($url);
					//log::info($data);
//					echo "+";
				//	db::store($type,$data,array('TITLE','CITY','ZIP','ALLSCRIPTS_ID'));
					//db::insertInto($type,$data,false,true);
					$toInsert[] = $data;
				}
			}
			if ($toInsert)
				db::insertArrayInto($type,$toInsert,true);
		}
	}

	public function csvSql()
	{
		$type = get_class($this);
		return array(/*
		"{$type}_ALL" => "
			SELECT 
				levenshtein_ratio(a.ORGANIZATION, ag.TITLE)	as MATCH_PERCENT,
				a.NAME,
				a.STATUS_LEVEL,
				a.ORGANIZATION,
				a.SITE,
				a.PHONE_NUMBER,
				a.MEMBER_SINCE,
				a.LAST_LOGGED_IN,
				a.URL,
				a.EMAIL,
				a.WEB_SITE,
				ag.TITLE		as GEOCODE_TITLE,
				ag.STATE		 as GEOCODE_STATE,
				ag.ZIP		 as GEOCODE_ZIP,
				ag.CITY		 as GEOCODE_CITY,
				ag.ADDRESS	 as GEOCODE_ADDRESS,
				ag.ADDRESS2	 as GEOCODE_ADDRESS2,
				ag.TELEPHONE as GEOCODE_TELEPHONE,
				ag.WEBSITE	 as GEOCODE_WEBSITE,
				ag.REVIEWS	 as GEOCODE_REVIEWS,
				ag.RATING	 as GEOCODE_RATING,
				ag.COUNTRY	 as GEOCODE_COUNTRY,
				ag.RAW_ADDRESS	as GEOCODE_RAW_ADDRESS,
				ag.URL		 as GEOCODE_SOURCEURL,
				ag.ALLSCRIPTS_ID

			FROM allscripts a
				INNER JOIN allscripts_geocode ag ON ag.allscripts_id = a.id 
		",*/
		"{$type}_BESTMATCH" => "
		SELECT 
				MATCH_PERCENT,
				a.NAME,
				a.STATUS_LEVEL,
				a.ORGANIZATION,
				a.SITE,
				a.PHONE_NUMBER,
				a.MEMBER_SINCE,
				a.LAST_LOGGED_IN,
				a.EMAIL,
				a.WEB_SITE,
				ag.TITLE		as GEOCODE_TITLE,
				ag.STATE		 as GEOCODE_STATE,
				ag.ZIP		 as GEOCODE_ZIP,
				ag.CITY		 as GEOCODE_CITY,
				ag.ADDRESS	 as GEOCODE_ADDRESS,
				ag.ADDRESS2	 as GEOCODE_ADDRESS2,
				ag.TELEPHONE as GEOCODE_TELEPHONE,
				ag.WEBSITE	 as GEOCODE_WEBSITE,
				ag.REVIEWS	 as GEOCODE_REVIEWS,
				ag.RATING	 as GEOCODE_RATING,
				ag.COUNTRY	 as GEOCODE_COUNTRY,
				ag.RAW_ADDRESS	as GEOCODE_RAW_ADDRESS,
				ag.URL		 as GEOCODE_SOURCEURL,
				ag.ALLSCRIPTS_ID

			FROM allscripts a
				INNER JOIN allscripts_geocode ag ON ag.allscripts_id = a.id 
				INNER JOIN (
									SELECT 
										MAX( levenshtein_ratio(ai.ORGANIZATION, aig.TITLE)) as MATCH_PERCENT,
										aig.ALLSCRIPTS_ID as aig_ALLSCRIPTS_ID,
										MIN(aig.id) as AIG_ID
									FROM allscripts ai
										INNER JOIN allscripts_geocode aig ON aig.allscripts_id = ai.id 
										GROUP BY aig.ALLSCRIPTS_ID 
								) tbl ON AIG_ID = ag.id
									
		"	
		);
	}
}


$r= new allscripts_geocode();

 $r->parseCommandLine();



