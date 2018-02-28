<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class vcahospitals extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=8;
		$this->debug=false;
		
		//
	/*	
		
		
			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			

		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='vcahospitals' and parsed = 1 ");

		db::query("	DROP TABLE vcahospitals"); 
		db::query("UPDATE raw_data set parsed = 0 where type='vcahospitals' and parsed = 1 ");
	*/
		$this->loadUrl("http://www.vcahospitals.com/tools/markers.php?map-lat=39.0558235&map-lng=-95.6890185&map-filter=all&map-limit=200000&map-value=undefined");
		
		// http://www.vcahospitals.com/tools/markers.php?map-lat=38.4404674&map-lng=-122.7144314&map-filter=all&map-limit=6&map-value=undefined

		//db::query("UPDATE load_queue set processing = 0 where type='vcahospitals' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='vcahospitals' and parsed = 1 ");
		db::query("DROP TABLE $type");	
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();
		// <marker id="9121551" au_id="877" shortname="welborn" name="VCA Welborn Animal Hospital" address="7860 Washington Avenue, Kansas City, KS 66112" phone="913-334-6770" distance="49.862888626341" lat="39.118745" lng="-94.762843"/>
		
		if (preg_match("/vcahospitals.com\/tools\/markers.php/",$url))
		{
			$urls = array();
			foreach ($x->query("//markers/marker") as $node)
			{
				$data = array();
				
				foreach ($node->attributes as $attr)
				{
					$data[strtoupper($attr->name)] = $attr->value;
				}

				if (!empty($data['NAME']))
				{
					$data = array_merge($data, $ap->parse($data['ADDRESS']));
					$data['XID'] = $data['ID']."-".$data['AU_ID'];
					
					$data['WEBSITE'] = "http://www.vcahospitals.com/".$data['SHORTNAME'];
					$urls[] = $data['WEBSITE']."?XID=".$data['XID'];

					unset($data['DISTANCE']);
					unset($data['ID']);
					unset($data['AU_ID']);
					unset($data['SHORTNAME']);
					unset($data['LAT']);
					unset($data['LNG']);

					log::info($data['NAME']);		
					db::store($type,$data,array('XID'));	
				}		
			}
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			parse_str(parse_url($url,PHP_URL_QUERY),$q); // address and zip	
			$bean = R::FindOne('vcahospitals', "XID = '{$q['XID']}'");

			foreach ($x->query("//div[@class='tel']/span[text()='Fax']") as $node)
			{
				$bean->FAX = preg_replace("/Fax:/", "", $node->parentNode->textContent);
			}

			log::info("Secondary store");
			log::info($bean->export());
			R::store($bean);
		}

	

	}
}

$r= new vcahospitals();
$r->parseCommandLine();

