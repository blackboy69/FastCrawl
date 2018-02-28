<?
include_once "config.inc";

class zocdoc extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		$this->threads=2;
		$this->noProxy=false;
		$this->switchProxy();
		$this->timeout=10;
		
		$type = get_class();	
		foreach(array(153,104,101,98,130,386,122,117) as $id)		
		{
			$this->loadUrlsByZip("https://www.zocdoc.com/search/searchresults?SpecialtyId=$id&IsSearchingByName=false&Address=%ZIP%&InsuranceId=-2&InsurancePlanId=-1&ProcedureId=75&ProcedureChanged=false&Gender=-1&DayFilter=0&LanguageId=-1&LanguageChanged=false&TimeFilter=AnyTime&PatientTypeChild=&SortSelection=0&HospitalId=-1&DirectoryType=&Offset=0&ReferrerType=&SubmitSearchClicked=false&HasNoSearchResults=false&MapSearchBoundaries=&MapSearchCenter=&_=1472083284979");
		}
		
		//https://www.zocdoc.com/doctor/alejandro-montes-md-42873?LocIdent=30493&reason_visit=75&insuranceCarrier=-2&insurancePlan=-1' and type = '$type' ");
		//$this->loadUrl("https://www.zocdoc.com/doctor/alejandro-montes-md-42873?LocIdent=30493&reason_visit=75&insuranceCarrier=-2&insurancePlan=-1",true);
	
	
		//db::query("UPDATE load_queue set processing = 0 where type='$type' and processing=1 AND url IN (SELECT SOURCE_URL  COLLATE utf8_unicode_ci  as SOURCE_URL from $type where FIRST_NAME = '' or FIRST_NAME is null)");
		
		//db::query("UPDATE load_queue set processing = 0 where type='$type' and processing=1 AND url NOT IN (SELECT url from RAW_DATA where type='$type')");

		
	}

	public static function loadCallBack($url,$html,$type)
   {
	   if (strpos($html,"This request was blocked by the security rules")> 0)
	   {
			log::info("This request was blocked by the security rules!");
		    $html="";
	   }
	   parent::loadCallBack($url,$html,$type);
   }
	
	static function parse($url,$html)
	{
		$t=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$np = new Name_Parser();
		$urls = array();

		$data = array();
	
		$x = new Xpath($html);
		
		if (strpos($html,"This request was blocked by the security rules")> 0)
	    {
			log::info("This request was blocked by the security rules!");				
			db::query("UPDATE  load_queue set processing=0 where url = '$url' and type= '$type'");
			return;
		}
		
		if (strpos($url, "zocdoc.com/search") > 0)
		{			
			$allData = json_decode (str_replace("for(;;);","",$html),true);
			
			foreach($allData['model']['Doctors'] as $doc)
			{				
			
				$urls[] = self::relative2absolute($url, $doc['ProfUrl']); // next links
			}
			
			if (! empty($allData['pagination']['NextQuery']))
			{
				$urls[] = self::relative2absolute($url, "/search/searchresults?{$allData['pagination']['NextQuery']}");
			}
			$t->loadUrlsByArray($urls);
		}
		else
		{

			foreach($x->query("//*[@data-test='doctor-profile-name']") as $node)
			{
				$data = array_merge($data, $np->parse($node->textContent));
			}
			
			
			foreach($x->query("//div[contains(@class,'profile-doctor-name')]//h2") as $node)
			{
				$data['TITLE'] =$node->textContent;
			}		
			if (empty($data['TITLE']))
			{
				foreach($x->query("//span[@class='title']//h2") as $node)
				{
					$data['TITLE'] =$node->textContent;
				}
			}
			
			foreach($x->query("//div[contains(@class,'profile-doctor-name')]//h2") as $node)
			{
				$data['SPECIALTY'] = self::cleanup($node->textContent);
			}
			if (empty($data['SPECIALTY']))			
			{
				foreach($x->query("//li[contains(@class,'specialty')]//h2") as $node)
				{
					$data['SPECIALTY'] = self::cleanup($node->textContent);
				}
			}
			
			
			foreach($x->query("//*[@data-test='practice-name']") as $node)
			{
				$data['COMPANY'] = self::cleanup($node->textContent);
			}
			
			foreach($x->query("//section[contains(@class,'profile-qualifications-panel')]//div[@class='sg-row']") as $node)
			{
				$x2= new XPath($node);
				$k=$v="";
				foreach($x2->query("//p[contains(@class,'sg-title')]") as $node2)
				{
					$k=self::cleanup($node2->textContent);
				}
				if (!empty($k))
				{
					foreach($x2->query("//div[contains(@class,'sg-columns sg-small-8')]") as $node2)
					{
						$v=self::cleanup($node2->textContent);
					}
						
					
					$data[$k]=$v;
				}				
			}			
			
			$earliest=PHP_INT_MAX;
			$latest=0;
			
			$allReviews=array();
			foreach($x->query("//p[@itemprop='datePublished']") as $node)
			{
				$k = strtotime($node->textContent);
				$allReviews[$k]=$node->textContent;
				
				if ($k < $earliest)
					$earliest=$k;
				
				if ($k > $latest)
					$latest=$k;				
			}
			if (!empty($allReviews))
			{
				$data['FIRST_REVIEWED'] =$allReviews[$earliest];
				$data['LAST_REVIEWED'] =$allReviews[$latest];
			}
			
			foreach($x->query("//*[@itemprop='aggregateRating']//meta") as $node)
			{
				$k = self::cleanup($node->getAttribute("itemprop"));
				$v = self::cleanup($node->getAttribute("content"));
				$data[$k] = $v;
			}		

			foreach($x->query("//*[@class='location-field']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
				break;
			}
			
			foreach($x->query("//*[@*='address']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
				break;
			}

			$phones = array();	
			foreach ($pp->parse($html) as $k=>$phone)
			{
				if (preg_match("/ /",$phone) && $phone != '(855) 962-3621') 
					$phones[] = $phone;
			}
			$data = array_merge($data,$pp->parse($phones));

			//email
			//$data = array_merge($data,$ep->parse($html));
			

			$categories = array();
			foreach($x->query("//div[@class='specs']//li") as $node)
			{
				$categories[] = $node->textContent;
			}

			foreach($x->query("//*[@class='sg-side-short-list specialties-links-id']//li") as $node)
			{
				$categories[] = $node->textContent;
			}
			if (!empty($categories))
				$data['CATEGORIES'] = join(", ",$categories);

			


			unset($data["RAW_ADDRESS"]);
			$data['SOURCE_URL'] = $url;				
			

			log::info($data);
			//log::info($data['COMPANY']);
			
			if (isset($data['FIRST_NAME']))
				db::store($type,$data,array('FIRST_NAME','LAST_NAME','ADDRESS','ZIP'));


		}
	}
}
function sksort(&$array, $subkey="id", $sort_ascending=false) {

    if (count($array))
        $temp_array[key($array)] = array_shift($array);

    foreach($array as $key => $val){
        $offset = 0;
        $found = false;
        foreach($temp_array as $tmp_key => $tmp_val)
        {
            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
            {
                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                            array($key => $val),
                                            array_slice($temp_array,$offset)
                                          );
                $found = true;
            }
            $offset++;
        }
        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
    }

    if ($sort_ascending) $array = array_reverse($temp_array);

    else $array = $temp_array;
}
//db::query("UPDATE  raw_data set parsed=0 where type='zocdoc' and parsed=1 ");
/*db::query("DROP TABLE zocdoc ");
*/
$r = new zocdoc();
$r->parseCommandLine();
