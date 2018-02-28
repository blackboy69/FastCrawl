<?php

include_once "config.inc";

class aao extends baseScrape
{
   public static $_this=null;
	public static $numCalls = 0;
	public $proxies = array();

	public function runLoader()
   {
		
		$type = get_class();		

//		db::query("DELETE FROM load_queue where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");




		//db::query("UPDATE load_queue set processing = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type'");
		#db::query("DROP TABLE  $type");
		//db::query("DELETE FROM $type");

		//db::query("DELETE FROM load_queue where type='$type'");
	//	db::query("DELETE FROM raw_data where type='$type'");	
		
		$this->threads=1;
		$this->noProxy=true;
		
		$reqs = array();
      $result = mysql_query("SELECT CITY, STATE FROM geo.US_CITIES order by pop desc limit 500");        
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode(strtoupper($r[0]));
			$state = urlencode(ucfirst($r[1]));
			$toPost = "last_name=&subspecialty=ALL&city=$city&state=$state&zip=&radius=1&country_code=&name_search.x=25&name_search.y=10&name_search=Search";
			$req = new WebRequest("http://www.aao.org/find_eyemd.cfm?fuseaction=search&x=$city$state",$type,"POST",$toPost);
			$this->loadWebRequest($req);
			$this->queuedFetch();
			$this->parseData();
      }

      $result = mysql_query("SELECT CITY, STATE FROM geo.CANADIAN_CITIES order by pop desc limit 500");        
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode(strtoupper($r[0]));
			$state = urlencode(ucfirst($r[1]));
			$toPost = "last_name=&subspecialty=ALL&city=$city&state=&zip=&radius=1&country_code=CAN&name_search.x=25&name_search.y=10&name_search=Search";
			$req = new WebRequest("http://www.aao.org/find_eyemd.cfm?fuseaction=search&x=$city.CAN",$type,"POST",$toPost);
			$this->loadWebRequest($req);
			$this->queuedFetch();
			$this->parseData();
      }
   }
	
	static function parse($url,$html)
//	static function parseold($url,$html)
	{
		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query);
		
		if (isset($query['fuseaction']))
		{
			switch ($query['fuseaction'])
			{
				case "search":
					self::parseListings($url,$html);
					break;
				case "profile":
					self::parseDetails($url,$html);
					break;
				default:
					log::info("Unknown Action: ".$query['fuseaction']);
			}
		}
		else
		{
			log::info("Cannot Parse. Unknown URL: $url");
		}
	}
	
	static function parseListings($url,$html)
	{
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		

		$urls = array();


		foreach ($x->query("//a") as $node)
		{
			$href = self::relative2absolute($url, $node->getAttribute("href"));

			parse_str(parse_url($href,PHP_URL_QUERY),$query);
			$fa = isset($query['fuseaction'])? $query['fuseaction'] : null ;
			$fa = isset($query['fuseaction'])? $query['fuseaction'] : null ;

			if  ($fa ==  'profile')				
			{
				$urls[$href] = $href;
			}

			if ($fa=='search')
			{
				if(preg_match("/[0-9]+/", $node->textContent))
				{
					$urls[$href] = $href;
				}
			}
		}
		if (sizeof($urls)>0)
		{
			self::getInstance()->loadUrlsByArray($urls);	
			return true;
		}
		return false;

	}

	static function parseDetails($url,$html)
	{
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$html = str_replace("<br />","\n",$html);


		$html = str_replace("&nbsp;"," ",$html);
		$html = str_replace("\t"," ",$html);

		$type = get_class();		

		$dom = new DOMDocument();
		$x = new HtmlParser($html);
		$data = array();

		foreach ($x->query("//table[@width='470']/tr[2]") as $node)
		{	
			$data['Name'] = self::reduceExtraSpaces($node->textContent);
		}
		if (isset($data['Name']))
		{
			
			foreach ($x->query("//table[@width='470']/tr") as $node)
			{	
				$x2 = new HtmlParser($node->c14n());
				$key = $value = "";
				$i=0;
				foreach ($x2->query("//td") as $node2)
				{		
					$i++;
					$td = $node2->textContent;
					if ($i == 2)
					{
						$key= trim(str_replace(":","",$td));
					}
					if ($i == 3)
					{
						$value=$td;
					}
				}
				if(!empty($key))
				{
					$data[$key]=$value;
				}
			}
			
			$data = self::PhoneAddressParse($data, "Primary Office");
			$data = self::PhoneAddressParse($data, "Second Office");
			$data = self::PhoneAddressParse($data, "Third Office");
			
			db::store($type,$data,array('Name','Primary Office'));
			//log::info($data['Name']);
			echo ".";
		}
		else
		{
			log::info("No data for $url");
		}


	}

	static function PhoneAddressParse($data, $type="Primary Office")
	{
		if (isset($data[$type]))
		{
			$ap = new Address_Parser();
			foreach($ap->parse($data[$type]) as $k=>$v)
			{
				$data["$type $k"] = $v;
			}
			if (preg_match("/Ph: ([0-9\)\(\- ]+)/",$data[$type],$matches))
			{
				$data["$type Telephone"]=$matches[1];
			}
			unset ($data["$type Raw Address"]);
		}
		return $data;

	}

	static function reduceExtraSpaces($str)
	{
		return trim(preg_replace("/(\t|\n|\r| )+/"," ",$str));
	}
	
	static function cleanup($str)
	{
		while (preg_match("/  /",$str))
		{
			$str = preg_replace("/  /i"," ",$str);
		}
		return trim($str);
	}

	

}

$r = new aao();
$r->parseCommandLine();

