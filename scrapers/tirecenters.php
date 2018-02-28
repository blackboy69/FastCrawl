<?
include_once "config.inc";

class tirecenters extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		db::query("DROP TABLE $type");

		$soap = new SoapClient("http://www.tirecenters.com/tirecenters_DEV/CFC/locations.cfc?wsdl"); 
	
		foreach(db::query("SELECT distinct state FROM geo.locations ",true) as $row)
      {
			log::info($row['state']);

			foreach ($soap->getlocs($row['state'],"ALL") as $res)
			{
				$data=array();
				$data['ADDRESS1'] = $res[0];
				$data['ADDRESS2'] = $res[1];
				$data['CITY'] = $res[2];
				$data['EMAIL'] = $res[3];
				$data['PHONE'] = $res[4];
				$data['STATE'] = $res[5];
				$data['ZIP'] = $res[6];
				$data['XXID'] = $res[7];



				$data['SOURCE_URL'] = "http://www.tirecenters.com/tirecenters_DEV/CFC/locations.cfc?wsdl::getlocs({$row['state']},ALL);";
				$data = db::normalize($data);
				log::info($data);					
				db::store($type,$data,array('XXID'));	
			}
		}

	}
}
$r= new tirecenters();
$r->parseCommandLine();