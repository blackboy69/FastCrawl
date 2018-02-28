<?
include_once "config.inc";

class google_partners extends baseScrape
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




*/
		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		//db::query("DROP TABLE $type");	
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
//..		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='google_partners' ");
//		

		//$this->noProxy=true;

//
/*
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
db::query("DROP TABLE $type");	*/
	//	db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		$this->loadUrl("https://partners.clients6.google.com/v2/companies?requestMetadata.locale=en-US&pageSize=10&pageToken=10%7C0&view=CV_GOOGLE_PARTNER_SEARCH&minMonthlyBudget.currencyCode=USD&minMonthlyBudget.units=500&maxMonthlyBudget.currencyCode=USD&maxMonthlyBudget.units=2500&languageCodes=en&address=United%20States&orderBy=address&gpsMotivations=GPSM_HELP_WITH_ADVERTISING&gpsMotivations=GPSM_HELP_WITH_WEBSITE&key=AIzaSyDkOqB5IqBU9kpffCcvDTx-Drl-ELLgsh4");

	}
	
	static function parse($url,$html)
	{
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$json = json_decode($html,true);
		$ap = new  Address_Parser();
		$pp = new Phone_Parser();
		

		if (isset($json["nextPageToken"]))
		{
			$npt = urlencode($json["nextPageToken"]);

			$thiz->loadUrl("https://partners.clients6.google.com/v2/companies?requestMetadata.locale=en-US&pageSize=10&pageToken=$npt&view=CV_GOOGLE_PARTNER_SEARCH&minMonthlyBudget.currencyCode=USD&minMonthlyBudget.units=500&maxMonthlyBudget.currencyCode=USD&maxMonthlyBudget.units=2500&languageCodes=en&address=United%20States&orderBy=address&gpsMotivations=GPSM_HELP_WITH_ADVERTISING&gpsMotivations=GPSM_HELP_WITH_WEBSITE&key=AIzaSyDkOqB5IqBU9kpffCcvDTx-Drl-ELLgsh4");
		}

		foreach ($json["companies"] as $company)
		{

			foreach ($company["locations"] as $location)
			{
				$data = array();
				$data["COMPANY_NAME"] = $company["localizedInfos"][0]["displayName"];
				$data = array_merge($data, $ap->parse($location["address"]));

				if (isset($company["convertedMinMonthlyBudget"]["units"]))
					$data['Min_Monthly_Budget'] = $company["convertedMinMonthlyBudget"]["units"]." ". $company["convertedMinMonthlyBudget"]["currencyCode"];
				else if (isset($company["convertedMinMonthlyBudget"]["units"]))
					$data['Min_Monthly_Budget'] = $company["originalMinMonthlyBudget"]["units"]." ". $company["originalMinMonthlyBudget"]["currencyCode"];

				$data['PROFILE_URL'] = $company["publicProfile"]['url'];
				$data['WEB_SITE'] = $company["websiteUrl"];
				$data['industries'] = join(", ", $company["industries"]);
				$data['services'] = join(", ", $company["services"]);

				$certsAr = array();
				foreach($company['certificationStatuses'] as $certs)
				{
					$certsAr[] = $certs['type'];
				}
				$data['certification'] = join(", ", $certsAr);
      
				
				static $siteCache = array();
				if (!isset ($siteCache[$data['WEB_SITE']]))
					$siteCache[$data['WEB_SITE']] = $thiz->Get($data['WEB_SITE']);

				$dom = new DOMDocument();

				// for some strange reason, php dom turns non breaking spaces into this  ┬а
				@$dom->loadHTML($siteCache[$data['WEB_SITE']]);
				$phoneText = $dom->documentElement->textContent;
				$data = array_merge($data, $pp->parse($phoneText));

				$data = db::normalize($data);
				unset($data["ID"]);
				
				$data['SOURCE_URL'] = $url;	
				
				print_r($data);
				db::store($type,$data,array('COMPANY_NAME', 'RAW_ADDRESS'));
			}
		}

	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='google_partners' and parsed=1 ");
/*db::query("DROP TABLE google_partners ");
*/
$r = new google_partners();
$r->parseCommandLine();
