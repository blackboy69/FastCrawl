<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class optometrists_ab_ca extends baseScrape
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
		db::query("UPDATE raw_data set parsed = 0 where type='optometrists_ab_ca' and parsed = 1 ");
	
	//	
	

//		db::query("UPDATE raw_data set parsed = 0 where type='optometrists_ab_ca' and parsed = 1 ");
//		db::query("	DROP TABLE optometrists_ab_ca"); 

		// load all cities 
		/*$cities  = array ('100 Mile House', 'Abbotsford', 'Armstrong', 'Burnaby', 'Burns Lake', 'Campbell River', 'Castlegar', 'Chase', 'Chemainus', 'Chetwynd', 'Chilliwack', 'Clearwater', 'Cobble Hill', 'Comox', 'Coquitlam', 'Courtenay', 'Cranbrook', 'Creston', 'Dawson Creek', 'Delta', 'Duncan', 'Fernie', 'Fort Nelson', 'Fort St. John', 'Golden', 'Grand Forks', 'Hope', 'Invermere', 'Kamloops', 'Kelowna', 'Kimberley', 'Kitimat', 'Ladysmith', 'Lake Country', 'Lake Cowichan', 'Langford', 'Langley', 'Maple Ridge', 'Merritt', 'Mill Bay', 'Mission', 'Nakusp', 'Nanaimo', 'Nelson', 'New Westminster ', 'North Vancouver', 'Osoyoos', 'Parksville', 'Pender Island', 'Penticton', 'Pitt Meadows', 'Port Alberni', 'Port Coquitlam', 'Port Hardy', 'Port Moody', 'Powell River', 'Prince George', 'Prince Rupert', 'Princeton', 'Qualicum', 'Quesnel', 'Revelstoke', 'Richmond', 'Saanichton', 'Salmon Arm', 'Salt Spring Island', 'Sardis', 'Sechelt', 'Sicamous', 'Sidney', 'Smithers', 'Sooke', 'Squamish', 'Summerland', 'Surrey', 'Terrace', 'Trail', 'Tsawwassen', 'Tumbler Ridge', 'Vancouver', 'Vernon', 'Victoria', 'West Kelowna', 'West Vancouver', 'Westbank', 'Whistler', 'White Rock', 'Williams Lake');

		$state = "BC";
      foreach ($cities as $city)
      {
			$urls[] = "http://$state.optometrists_ab_ca.ca/find-a-doctor-results?province=$state&city=$city&postal=&lastname=&fad_submit=SUBMIT";
      }
      return  $this->loadUrlsByArray($urls);   
		*/

		for ($i=1;$i<=46;$i++)
		{
			$url = "http://www.optometrists.ab.ca/content.php?id=74&page=$i";
			$data = "member_search=Search&member_quick_search=&member_search_display_size=25&member_search_page=$i&member_search_sortby=company&member_search_sortdirection=&member_search_alpha=&member_search_group_match=any&member_search_groups%5B%5D=3137&member_search_groups%5B%5D=3197&member_search_groups%5B%5D=3181&member_search_groups%5B%5D=3169&member_search_groups%5B%5D=3164&member_search_groups%5B%5D=3316&member_search_groups%5B%5D=3551&member_search_groups%5B%5D=3214&member_search_groups%5B%5D=3173&member_search_groups%5B%5D=4302&member_search_groups%5B%5D=3213&member_search_groups%5B%5D=3131&member_search_groups%5B%5D=3156&member_search_groups%5B%5D=3184&member_search_groups%5B%5D=3140&member_search_groups%5B%5D=4658&member_search_groups%5B%5D=3211&member_search_groups%5B%5D=3299&member_search_groups%5B%5D=3190&member_search_groups%5B%5D=3170&member_search_groups%5B%5D=3146&member_search_groups%5B%5D=3194&member_search_groups%5B%5D=3231&member_search_groups%5B%5D=4860&member_search_groups%5B%5D=3217&member_search_groups%5B%5D=3182&member_search_groups%5B%5D=3139&member_search_groups%5B%5D=3278&member_search_groups%5B%5D=4871&member_search_groups%5B%5D=3153&member_search_groups%5B%5D=3147&member_search_groups%5B%5D=3223&member_search_groups%5B%5D=3157&member_search_groups%5B%5D=3364&member_search_groups%5B%5D=3243&member_search_groups%5B%5D=3237&member_search_groups%5B%5D=3186&member_search_groups%5B%5D=3167&member_search_groups%5B%5D=3275&member_search_groups%5B%5D=3220&member_search_groups%5B%5D=3318&member_search_groups%5B%5D=3134&member_search_groups%5B%5D=3202&member_search_groups%5B%5D=3161&member_search_groups%5B%5D=3160&member_search_groups%5B%5D=3168&member_search_groups%5B%5D=3159&member_search_groups%5B%5D=3130&member_search_groups%5B%5D=3177&member_search_groups%5B%5D=3467&member_search_groups%5B%5D=3334&member_search_groups%5B%5D=4861&member_search_groups%5B%5D=3150&member_search_groups%5B%5D=3212&member_search_groups%5B%5D=1131&member_search_groups%5B%5D=4022&member_search_groups%5B%5D=3277&member_search_groups%5B%5D=3185&member_search_groups%5B%5D=3281&member_search_groups%5B%5D=3155&member_search_groups%5B%5D=4337&member_search_groups%5B%5D=3152&member_search_groups%5B%5D=3461&member_search_groups%5B%5D=3221&member_search_groups%5B%5D=3222&member_search_groups%5B%5D=3216&member_search_groups%5B%5D=3141&member_search_groups%5B%5D=3460&member_search_groups%5B%5D=3395&member_search_groups%5B%5D=3175&member_search_groups%5B%5D=3133&member_search_groups%5B%5D=3196&member_search_groups%5B%5D=3187&member_search_groups%5B%5D=3176&member_search_groups%5B%5D=3165&member_search_groups%5B%5D=4782&member_search_groups%5B%5D=3180&member_search_groups%5B%5D=3276&member_search_groups%5B%5D=3166&member_search_groups%5B%5D=3242&member_search_groups%5B%5D=3233&member_search_groups%5B%5D=3239&member_search_groups%5B%5D=3138&member_search_groups%5B%5D=3178&member_search_groups%5B%5D=3529&member_search_groups%5B%5D=3494&member_search_groups%5B%5D=3192&member_search_groups%5B%5D=3193&member_search_groups%5B%5D=3136&";
		
			$this->loadPostUrl($url,$data);
		}
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

		foreach ($xListing->query("//ul[@id='members_search-results']//table[@class='vcard']") as $nodeListing)
		{			
			$x = new XPath($nodeListing);	
			$data = array();
			$name = array();
			foreach ($x->query("//span[@class='given-name']") as $node)
			{
				$name[] = $node->textContent;
			}
			foreach ($x->query("//span[@class='family-name']") as $node)
			{
				$name[] = $node->textContent;
			}
			$data['DOCTOR_NAME'] = join(" ",$name);
			
			foreach ($x->query("//span[@class='fn org']") as $node)
			{
				$data['COMPANY_NAME'] = $node->textContent;
			}

			foreach ($x->query("//span[@class='street-address']") as $node)
			{
				$data['ADDRESS'] = $node->textContent;
			}

			foreach ($x->query("//span[@class='locality']") as $node)
			{
				$data['CITY'] = $node->textContent;
			}

			foreach ($x->query("//span[@class='region']") as $node)
			{
				$data['STATE'] = $node->textContent;
			}

			foreach ($x->query("//span[@class='postal-code']") as $node)
			{
				$data['ZIP'] = $node->textContent;
			}
			
			$phone = array();
			foreach ($x->query("//span[@class='tel']") as $node)
			{
				$phone[] = $node->textContent;
			}
			
			$data = array_merge($data,$pp->parse( join(",", $phone)));

			foreach ($x->query("//a[text()='view website']") as $node)
			{
				$data['WEBSITE'] = $node->getAttribute("href");
			}	
log::info($data);
			if(isset($data['ADDRESS']) && isset($data['ZIP']))
				db::store($type,$data,array('ADDRESS','ZIP'));	
			else
				log::error("NOT SAVED");		
		}
	}
}

$r= new optometrists_ab_ca();
$r->parseCommandLine();

