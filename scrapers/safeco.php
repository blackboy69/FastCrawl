<?
include_once "config.inc";


class safeco extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		

//		$this->noProxy=false;
	//	$this->proxy='localhost:9666';
#$this->debug=true;
//	db::query("DELETE FROM load_queue  where type='$type' ");
	//db::query("DELETE FROM raw_data where type='$type' ");
	//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		//db::query("DROP TABLE $type ");
//		$this->threads= 1;
		
		$this->loadUrlsByLocation('http://www.safeco.com/omapps/ContentServer?pagename=SafecoInsurance/FAA/siFAAWebService&FAAtype=location&FAAZip=%ZIP%&Fproduct=08&Fdist=100&FAArows=0&Fname=null&Fastate=null', $state='',$max=5000);		

   }


	static function parse($url,$xml)
	{
		$type = get_class();		

		$xTop = new Xpath($xml);		
		$dataIn =xml2array( $xml);
		$ap = new Address_Parser();
		$agencies = $dataIn['soapenv:Envelope']['soapenv:Body']['Agencies'];
		
		foreach ($agencies as $agency)
		{
			$data = array();

			foreach ($agency as $k => $v)
			{
				if (is_array($v)) continue;

				$data[$k] = $v;
			}
			$data = db::normalize($data);

			$data = array_merge($data, $ap->parse(join(",", array_values( $agency['Address'] ))));
			$data['SOURCE_URL'] = $url;
			if (array_key_exists('AGENCY_ID', $data))
			{
				log::info($data);
				db::store($type, $data,array('AGENCY_ID'),false);
			}
		}
	}


}
$r = new safeco();
$r->ParseCommandLine();
