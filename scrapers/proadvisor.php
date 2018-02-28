<?
include_once "config.inc";

class proadvisor extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		
		$type = get_class();		
/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
		
		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");

		db::query("DELETE FROM load_queue where type='$type' and processing=0");
		db::query("DELETE FROM raw_data where type='$type' and parsed = 0 ");
		db::query("DROP TABLE $type");	
		

		//$this->proxy = "localhost:9666";


//		
//		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
		
*/
		//db::query("delete from raw_data where type='proadvisor' and LENGTH (html) < 2750 and URL like 'http://proadvisor.intuit.com/fap/fap_profile_summary.jsp%'");

		$this->noProxy=true;
		$this->allowRedirects = false;
		$this->threads=1;
		//$this->useCookies = true;
		//$this->timeout = 5;


		$urls = array();
		for ($i = 1 ; $i<100000 ; $i++)
		{
			$urls[] = "http://proadvisor.intuit.com/fap/fap_profile_summary.jsp?proadvisor_id=$i";

			if (($i%1000) == 0)
			{
				$this->loadUrlsByArray($urls);
				$urls = array();
			}
		}
		
		$this->queuedFetch();
	}

	public static function parse($url,$html)
	{
		$query = array();
		$type = get_class();		
		$thiz = self::getInstance();		
		$host = parse_url($url,PHP_URL_HOST);
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();

		$data = array();
		$data['xid'] = $xid = $id = $query['proadvisor_id'];

		if (preg_match("/fap_profile_summary.jsp/",$url))
		{
			if (strlen($html) < 2750)
			{
				echo '>';
				return;
			}

			//TODO PARSE profile summary info
			$x = new Xpath($html);
			foreach($x->query("//div[@class='resultInfoName']") as $node)
			{
				$data['Name'] = trim($node->textContent);
			}

			foreach($x->query("//div[@class='resultInfoTagline']") as $node)
			{
				$data['Tagline'] = trim($node->textContent);
			}

			foreach($x->query("//div[@class='resultInfoCompany']") as $node)
			{
				$data['Company Name'] = trim($node->textContent);
			}

			foreach($x->query("//div[@class='resultInfoText']") as $node)
			{
				$label = $value = "";

				$x2 = new Xpath($node);			
				foreach($x2->query("//span[@class='resultDataLabel']") as $labelNode)
				{

					$label = str_replace(":", "", $labelNode->textContent);
				}
				foreach($x2->query("//span[@class='resultData']") as $valueNode)
				{
					$value = $valueNode->textContent;
				}
				if (!empty($label))
				{
					$data[$label]  =  $value;
				}
			}

			$data['Source Url'] = $url;			
		}
		else
		{
			// TODO: PARESE AND MERGE ADDRESS INFO WITH PROFILE INFO

			if (preg_match ("/maps\?q=(.+)&amp/",$html,$matches))
			{
				$data = array_merge($data, $ap->parse($matches[1]));
			}
			$data['Source Url 2'] = $url;
		}

		$dataFiltered = array();
		foreach($data as $k => $v)
		{
			$dataFiltered[preg_replace("/[^A-Za-z0-9]/","_",strtoupper($k))] = $v;
		}
		$data = $dataFiltered;


		if ( isset($data['NAME']) || isset($data['RAW_ADDRESS']) )
		{		
			$bean = R::findOne($type, ' xid = ? ',array($xid));
			if (is_object($bean))
			{
				$data =  array_merge( $bean->export(), $data);
				unset($data['id']);
			}
			
			
			$thiz->loadUrl("http://proadvisor.intuit.com/fap/fragments/fap_google_map.jsp?proadvisor_id=$xid");
			log::info($data);
			self::store($data);

		}
	}

	public static function store($data)
	{

		$type = get_class();		

		{
			try {				
				db::store($type,$data,array('XID'),true);
			}
			catch(Exception $e)
			{
				log::error ("Cannot store ".$data['NAME']);
				log::error($e);
				exit;
			}		
		}
	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='proadvisor' and parsed=1 ");
/*db::query("DROP TABLE proadvisor ");
*/
$r= new proadvisor();
$r = new proadvisor();
$r->parseCommandLine();

