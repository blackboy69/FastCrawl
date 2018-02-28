<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class optometrists_bc_ca extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
	//	$this->noProxy=false;
//		$this->proxy = "localhost:9666";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		
		//
	/*			
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");		*/
		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='optometrists_bc_ca' and parsed = 1 ");
	
	//	
	

//		db::query("UPDATE raw_data set parsed = 0 where type='optometrists_bc_ca' and parsed = 1 ");
//		db::query("	DROP TABLE optometrists_bc_ca"); 

		// load all cities 
		/*$cities  = array ('100 Mile House', 'Abbotsford', 'Armstrong', 'Burnaby', 'Burns Lake', 'Campbell River', 'Castlegar', 'Chase', 'Chemainus', 'Chetwynd', 'Chilliwack', 'Clearwater', 'Cobble Hill', 'Comox', 'Coquitlam', 'Courtenay', 'Cranbrook', 'Creston', 'Dawson Creek', 'Delta', 'Duncan', 'Fernie', 'Fort Nelson', 'Fort St. John', 'Golden', 'Grand Forks', 'Hope', 'Invermere', 'Kamloops', 'Kelowna', 'Kimberley', 'Kitimat', 'Ladysmith', 'Lake Country', 'Lake Cowichan', 'Langford', 'Langley', 'Maple Ridge', 'Merritt', 'Mill Bay', 'Mission', 'Nakusp', 'Nanaimo', 'Nelson', 'New Westminster ', 'North Vancouver', 'Osoyoos', 'Parksville', 'Pender Island', 'Penticton', 'Pitt Meadows', 'Port Alberni', 'Port Coquitlam', 'Port Hardy', 'Port Moody', 'Powell River', 'Prince George', 'Prince Rupert', 'Princeton', 'Qualicum', 'Quesnel', 'Revelstoke', 'Richmond', 'Saanichton', 'Salmon Arm', 'Salt Spring Island', 'Sardis', 'Sechelt', 'Sicamous', 'Sidney', 'Smithers', 'Sooke', 'Squamish', 'Summerland', 'Surrey', 'Terrace', 'Trail', 'Tsawwassen', 'Tumbler Ridge', 'Vancouver', 'Vernon', 'Victoria', 'West Kelowna', 'West Vancouver', 'Westbank', 'Whistler', 'White Rock', 'Williams Lake');

		$state = "BC";
      foreach ($cities as $city)
      {
			$urls[] = "http://$state.optometrists_bc_ca.ca/find-a-doctor-results?province=$state&city=$city&postal=&lastname=&fad_submit=SUBMIT";
      }
      return  $this->loadUrlsByArray($urls);   
		*/

		$this->loadUrl("http://www.optometrists.bc.ca/code/navigate.aspx?Id=88&action=1&cmbCity=&txtLastName=");
	}
	

	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		$ep = new Email_Parser();

		parse_str(parse_url($url,PHP_URL_QUERY),$qs);
		$data = array();
		log::info("In Listing");
		$xListing = new XPath($html);	
		$row = 0;

		foreach ($xListing->query("//table[@class='optometrists_list']/tr") as $nodeListing)
		{			
			if (preg_match("/Dr\. /", trim($nodeListing->textContent)) )
			{					
				$data['DOCTOR_NAME'] = trim($nodeListing->textContent);
			}
			else if (preg_match("/Phone:/",$nodeListing->textContent)) // Listing
			{
				$x = new XPath($nodeListing);	
				$nodeListingText =  trim(str_replace("\n", ", ", $nodeListing->textContent));
				list($data['COMPANY_NAME'],$junk) = explode(",", $nodeListingText);

				$data = array_merge($data,$ap->parse( $nodeListingText));
				$data = array_merge($data,$pp->parse( $nodeListingText));
				$data = array_merge($data,$ep->parse( $nodeListingText));				

				foreach ($x->query("//a") as $node)
				{
					if ( !$node->textContent == "Map")
						$data['WEBSITE'] = $node->getAttribute("href");
				}		
				
				$data['SOURCE_URL'] = $url;
				log::info($data);		

				if(isset($data['ADDRESS']) && isset($data['ZIP']))
					db::store($type,$data,array('ADDRESS','ZIP'));	
				else
					log::error("NOT SAVED");		

				$dn = $data['DOCTOR_NAME'];
				$data = array();
				$data['DOCTOR_NAME'] = $dn;
			}
		}
					
		if (! empty($data['NAME'] ) &&!empty($data['PHONE']))
		{

		}
	}
}

$r= new optometrists_bc_ca();
$r->parseCommandLine();

