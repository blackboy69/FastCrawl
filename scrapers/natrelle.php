<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class natrelle extends baseScrape
{
    public static $_this=null;
	
	
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		
		//
		
/*	
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1 and url like '%ws_DocDetail%' ");
		db::query("DROP TABLE $type");

		

				
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
*/	

		$geos = db::query("SELECT zip, max(lat) as lat ,max(lon) as lon from geo.locations where pop>35000 group by zip",true);
		$urls = array();
		foreach($geos as $geo)
		{
			$zip = $geo['zip'];
			$lat = $geo['lat'];
			$lon = $geo['lon'];
			/*
					$productIdMap[1] = 'b-otoxcosmetic';
		$productIdMap[2] = 'j-uvederm';
		$productIdMap[3] = 'n-atrelle';
		$productIdMap[7] = 'l-atisse';
		$productIdMap[10] = 'v-iviteskincare';*/

			$urls[]="http://www.botoxcosmetic.com/App_Controls/FindADoc/WebServices/ws_DocList.ashx?r=2.2&SearchType=1&CenterLatitude=$lat&CenterLongitude=$lon&Radius=50&ProductID=3&ZipCode=$zip&SortBy=0&showPictureID=true";
		
		}		
		$this->loadUrlsByArray($urls);
	}
	static function loadCallBack($url,$html,$arg3)
	{
		$type = get_class();	
		$host = parse_url($url,PHP_URL_HOST);

			if (preg_match("/The timeout period elapsed prior to obtaining a connection from the pool/", $html))
			{
				echo "waiting timeout";

				sleep(60);
				return;
			}
		
		baseScrape::loadCallBack($url,$html,$arg3);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$data = array();


		if (strpos($url, "/ws_DocList.ashx?")>0)
		{
			$urls = array();
			$l = @xml2array($html);

			$toparse = $l['doctors'];
		
			if (is_array($toparse))
			{

				if (is_array($l['doctors']['doctor']))
					$toparse = array_merge($toparse,$l['doctors']['doctor']);

				foreach ($toparse as $k=> $doctor)
				{			
					if (!is_array($doctor)) continue;
					if (empty($doctor["ShipToBioId"])) continue;

					$urls[] = "http://www.botoxcosmetic.com/App_Controls/FindADoc/WebServices/ws_DocDetail.ashx?r=2.3&shipToBioId=".$doctor['ShipToBioId'];
				}
				//print_r($urls);
				//print_r($l);
				$thiz->loadUrlsByArray($urls);
			}
		}
		else if (strpos($url,"/ws_DocDetail.ashx?")>0)
		{	
			$d = @xml2array($html);
			$doctor = $d['doctor'];

			if (isset($doctor['DisplayName']))
			{

				$fieldsToKeep = array(
					"DisplayName",
					"StreetAddress",
					"City",
					"StateName",
					"ZipCode",
					"TelephoneNumber",
					"WebsiteURL",
					"EmailAddress",
					"OfficeCoordinator",
					"OfficeCoordinatorPhone",
					"OfficeCoordinatorEmail",
					"PatientCareCoordinator",
					"PatientCareCoordinatorEmail",
					"PatientCareCoordinatorPhone",
					"YearPracticeStarted"
				);			
				foreach ($fieldsToKeep as $ftk)
				{
					$k = strtoupper(preg_replace("/([a-z])([A-Z])/","\\1_\\2",$ftk));
					$data[$k] = $doctor[$ftk];
				}

				// affiliations

				if (isset($doctor['Specialties']))
				{
					$data['SPECIALTIES'] = @join(", ", $doctor['Specialties']);
				}
					
				$pNum = 1;
				if (isset($doctor['practitioners']))
				{
					$toparse = $doctor['practitioners'];
					$toparse = array_merge($toparse,$doctor['practitioners']['practitioner']);

					foreach ($toparse as $k => $pData)
					{
						if (!is_array($pData)) continue;
						if (empty($pData["FirstName"])) continue;

						$PRACTITIONER = "PRACTITIONER_$pNum";

						$data["{$PRACTITIONER}_NAME"] = $pData["FirstName"]. " " .  $pData["LastName"];
						$data["{$PRACTITIONER}_DEGREE_TITLE"] = $pData["DegreeTitle"];

						if (isset($pData["Specialities"]))
						{
							$data["{$PRACTITIONER}_SPECIALTIES"] = @join(", ", $pData["Specialties"]);

						}
						if (isset($pData["Certifications"]))
						{
							$data["{$PRACTITIONER}_CERTIFICATIONS"] = @join(", ", $pData["Certifications"]);
						}
						
						if (isset($pData["Educations"]))
						{
							$data["{$PRACTITIONER}_EDUCATION"] = @join(", ", $pData["Educations"]["Education"]);
						}

						if (isset($pData["Languages"]))
						{
							$data["{$PRACTITIONER}_LANGUAGES_SPOKEN"] = @join(", ", $pData["Languages"]);
						}
						$pNum ++;
					}
				}			

				
				log::info($data);

				db::store($type,$data,array('DISPLAY_NAME','WEBSITE_URL','TELEPHONE_NUMBER'));
			}

		}
	}
}

$r= new natrelle();
$r->parseCommandLine();

