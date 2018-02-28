
<?
// US Postal Service Address Parser 
// ByronWhitlock@gmail.com
// 7-25-2011
//////////////////////////// BEGIN CONFIG //////////////////////////////////////////////////////// 
//if (!DEFINED('YAHOO_APPID')) DEFINE('YAHOO_APPID','qB62eVrV34FV3dWLS5wiDb7wn5iVrtm6ifANBR74om4X7DF1z0A9UrtQQ1YZIu2xbsAJ2AU');
DEFINE('USPS_USERID','147WHITL2048');
DEFINE('USPS_PASSWORD','067OX47FR941');
DEFINE('USPS_BASE_URL',"https://secure.shippingapis.com/ShippingAPI.dll");
//////////////////////////// END CONFIG  //////////////////////////////////////////////////////// 


// this class use the usps to first standardize the address, then we use yahoo place finder to geocode exact coordinates.
// yahoo placefinder allows up to 50,000 requests per day

Class AddressLookup
{
	
	// this function does a look up of an user inputed address 
	// 
	// Addressing verified by US POSTAL OFFICE
	//
	// Geocoding is done by YAHOO. thier TOS allows 25,000 lookups per day. 
	/*
		A successfull response will look like this:
			Array
			(
				 [Error] => 0
				 [Address2] => 123 WATERTROUGH RD STE B
				 [City] => SEBASTOPOL
				 [State] => CA
				 [Zip5] => 95472
				 [Zip4] => 
				 [latitude] => 38.395809
				 [longitude] => -122.851087
				 [county] => Sonoma County
			)
		An unsuccessfull response will look like this:
				Array
				(
					 [Error] => 1
					 [ErrorSource] => USPS Web Service
					 [ErrorMessage] => Address Not Found.  
				)
		Or this;  USPS match, but yahoo geocoder failed
				Array
				(
					 [Error] => 1
					 [Address2] => 124 WATERTROUGH RD
					 [City] => SEBASTOPOL
					 [State] => CA
					 [Zip5] => 95472
					 [Zip4] => 
					 [Source] => Yahoo Geocoding Web Service
					 [ErrorMessage] => No location parameters
				)

	*/
	public function lookup($addr,$zip)
	{
		$uspsRequest = $this->getUspsRequest($addr,$zip)	;
		$uspsResponse = file_get_contents($uspsRequest);
		$result = self::handleUspsResponse($uspsResponse);
		
		if (!$result['Error'])
		{
			$yahooRequestParams = join(",",array_values($result));		
			$result = array_merge($result, self::geoCode($yahooRequestParams));
		}
		return $result;
		
	}

	/*
		http://developer.yahoo.com/geo/placefinder/

		Find the coordinates of a street address:
			"1600 Pennsylvania Avenue, Washington, DC"

		Find the street address nearest to a point: 
			"38.898717, -77.03597"
	*/
	
	public static function reverseGeoCode($lat,$long)
	{
		$request = self::getYahooRequest("$lat,$long")."&gflags=R";
		$response = file_get_contents($request);

		return self::handleYahooResponse($response);
	}

	public static function geoCode($query)
	{
		$request = self::getYahooRequest($query);
		$response = file_get_contents($request);

		return self::handleYahooResponse($response);
	}
	
	private function getUspsRequest($addr,$zip)
	{
		// yes i know how to use XMLDoc
		// this is fast and easy and won't ever change.
		$userid = USPS_USERID;
		$baseUrl = USPS_BASE_URL;
		
		list($zip5,$zip4) = explode("-",$zip);
		return "$baseUrl?API=Verify&XML=".urlencode("<AddressValidateRequest USERID=\"$userid\"><Address ID=\"0\"><Address1></Address1><Address2>$addr</Address2><City></City><State></State><Zip5>$zip5</Zip5><Zip4>$zip4</Zip4></Address></AddressValidateRequest>");
		
	}
	
	private static function handleUspsResponse($response)
	{
		$dom = new DOMDocument();
		@$dom->loadXML($response);
		$x = new DOMXPath($dom);	
		
		$return = array();
		$return["Error"] = 1;
		
		foreach($x->query("//Error") as $errorNode)
		{
			$r=array();
			foreach($errorNode->childNodes as $e)
			{
				
				if (!empty($e->tagName))
				{
					$r[$e->tagName] = $e->textContent;
				}
			}	
			$return['ErrorSource'] = 'USPS Web Service';
			$return['ErrorMessage'] = $r['Description'];
			return $return;
		}

		//<AddressValidateResponse><Address ID="0"><Address2>234 WATERTROUGH RD</Address2><City>SEBASTOPOL</City><State>CA</State><Zip5>95472</Zip5><Zip4></Zip4></Address></AddressValidateResponse>
		// then the address is found.
		foreach($x->query("/AddressValidateResponse/Address") as $responseNode)
		{
			$errors = array();
			foreach($responseNode->childNodes as $r)
			{
				if (!empty($r->tagName))
				{
					$return[$r->tagName] = $r->textContent;
				}
			}
			$return["Error"] = 0;			
		}
		return $return;
	}
	
	
	private static function getYahooRequest($q)
	{
		$url = "http://where.yahooapis.com/geocode?q=" .urlencode($q);
		$url .= '&flags=P&appid='.urlencode(YAHOO_APPID);
		return $url;
	}
	
	private static function handleYahooResponse($response)
	{
	
		$return = array();
		$response = unserialize($response);

		if ($response['ResultSet']['Error'])
		{
			$return["Error"] = 1;	
			$return['Source'] = 'Yahoo Geocoding Web Service';
			$return['ErrorMessage'] = $response['ResultSet']['ErrorMessage'];
		}
		$results = $response['ResultSet']['Result'];
		
		if (sizeof($results) > 0)
		{
			foreach($results as $k=>$v)
			{
				$return[$k]=$v;
			}
		}
		return $return;
	}
}
