<?
include_once "config.inc";

class acatoday extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		
		$type = get_class();		
/*
		db::query("		
			UPDATE load_queue
			 
			 SET processing = 0

			 WHERE			 
				 url IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) < 3000 And url like 'http://www.google.com%')
				 AND type ='$type'
		");*/

		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		
*/
		//$this->proxy = "localhost:8888";
	//	$this->noProxy=false;

//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='acatoday' ");
		//db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");

		

		//db::query("DELETE FROM load_queue where type='$type'");
		//db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DROP TABLE $type");	

		//$this->useCookies = true;
		//$this->timeout = 5;
		// http://www.acatoday.com/find-a-veterinarian-animal-hospital/california/santa-cruz/?radius=50
//		$urls = array();
//      $result = mysql_query("SELECT CITY, name as STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc limit 1");    
		$reqs = array();
      $result = mysql_query("SELECT CITY, STATE FROM geo.US_CITIES");      
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode(strtoupper($r[0]));
			$state = urlencode(ucfirst($r[1]));
			$toPost = "searchType=general&CITY=$city&LASTNAME=&STATECD=$state&FIRSTNAME=&ZIP=&SKILLCDLST=&SKILLCDLST=";
			$reqs[] = new WebRequest("http://www.acatoday.org/search/memsearch/memsearch_results.cfm?x=$city$state",$type,"POST",$toPost);
      }

		$result = mysql_query("SELECT zip,state FROM geo.locations where pop > 25000");      
      while ($r = mysql_fetch_row($result))
      {
			$zip = urlencode(strtoupper($r[0]));
			$state = urlencode(ucfirst($r[1]));

			$toPost = "searchType=general&CITY=&LASTNAME=&STATECD=$state&FIRSTNAME=&ZIP=$zip&SKILLCDLST=&SKILLCDLST=";
			$reqs[] = new WebRequest("http://www.acatoday.org/search/memsearch/memsearch_results.cfm?x=$zip",$type,"POST",$toPost);
      }
		
		$this->loadWebRequests($reqs);
		/*
       $result = mysql_query("SELECT CITY, STATE FROM geo.US_CITIES INNER JOIN geo.states on code=STATE order by pop desc");      
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode(strtoupper($r[0]));
			$state = urlencode(ucfirst($r[1]));
			$toPost = "searchType=general&CITY=$city&LASTNAME=&STATECD=$state&FIRSTNAME=&ZIP=&SKILLCDLST=&SKILLCDLST=";
			$this->loadPostUrl("http://www.acatoday.org/search/memsearch/memsearch_results.cfm",$toPost);
      }
		*/
	}
	
	static function parse($url,$html)
	{
		$thiz =self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$urls = array();

		$data = array();		

		$xTop = new Xpath($html);
		foreach($xTop->query("//table[@class='doctors']//tr") as $listing)
		{
			$x = new Xpath($listing);

			// DOCTOR
			foreach($x->query("//th") as $node)
			{
				$data['COMPANY'] = self::cleanup(str_replace("Website", "", $node->textContent));
			}
			foreach($x->query("//a") as $node)
			{
				$data['WEB_SITE'] = $node->getAttribute('href');
				break;
			}
						
			$i = 1;
			foreach($x->query("//td") as $node)
			{
				switch($i)
				{
					case 1: //phone
						$data['PHONE'] = $node->textContent;
						break;

					case 2: //address
						$data = array_merge($data,$ap->parse($node->textContent));
						break;

					case 3: //specialities
						$categories = array();
						$data['CATEGORIES'] = self::cleanup($node->textContent);
						break;

					case 4: //conditions
						$data['CONDITIONS'] = self::cleanup($node->textContent);
						break;
				}

				$i++;
			}

			foreach($x->query("//div[@class='profSpecTitle']") as $node)
			{
				$data['TITLE'] = self::cleanup($node->textContent);
			}


			foreach($x->query("//div[@itemprop='aggregateRating']//meta") as $node)
			{
				$k = self::cleanup($node->getAttribute("itemprop"));
				$v = self::cleanup($node->getAttribute("content"));
				$data[$k] = $v;
			}		

			foreach($x->query("//div[@class='location-field']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
				break;
			}

			foreach($x->query("//div[@class='address']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
				break;
			}
			
			$data['SOURCE_URL']= $url;
			log::info($data);

			$data = db::normalize($data);
			if (!empty($data['PHONE']))
			{
				log::info($data);		
				db::store($type,$data,array('NAME','PHONE'));	
			}
		}
	}



}

//db::query("UPDATE  raw_data set parsed=0 where type='acatoday' and parsed=1 ");
/*db::query("DROP TABLE acatoday ");
*/
$r = new acatoday();
$r->parseCommandLine();
