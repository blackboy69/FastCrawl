<?
include_once "config.inc";

class aad extends baseScrape
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
			$this->proxy = "localhost:9666";
			db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
			
			db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
			db::query("UPDATE  raw_data set parsed=0 where type='$type' ");

			log::$errorLevel = ERROR_ALL;
		*/

		//db::query("DELETE FROM load_queue where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type'  ");
		//db::query("DROP TABLE $type");	
		//db::query("delete from raw_data where type='aad' and LENGTH (html) < 2750 and URL like 'http://aad.intuit.com/fap/fap_profile_summary.jsp%'");
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");	

		//log::$errorLevel = ERROR_ALL;

		$this->debug=false;
		$this->proxy='localhost:8888';
		$this->noProxy=false;

		$this->allowRedirects = true;

		$this->threads=4;
		$this->useCookies = false;
		//$this->timeout = 5;
		$posturl = "http://www.aad.org/find-a-derm";
		$this->referer['*'] = $posturl;

		//load the search form
		log::info("Load search form");
		$html = $this->Get($posturl);
		$searchForm = new HtmlParser($html);
	
		$reqs = array();
      $result = mysql_query("SELECT CITY, STATE FROM geo.US_CITIES order by pop desc limit 500");        
      while ($r = mysql_fetch_row($result))
      {
			$city = urlencode(strtoupper($r[0]));
			$state = urlencode(ucfirst($r[1]));

			// perform search with default parameters
			$data = $searchForm->loadViewState();
			$data['__EVENTTARGET']= '';
			$data['__EVENTARGUMENT']=	'';
			$data['__LASTFOCUS']='';
			$data['hdn_machinename']=	'WEBPROD';
			$data['ctl12$ucFCHeader$txtSearch']=	'Search';
			$data['ctl12$FCPartThree$ctl01$ddlCountry']=	'United States';
			$data['ctl12$FCPartThree$ctl01$txtLastName']=	'';
			$data['ctl12$FCPartThree$ctl01$txtCity']=	'';
			$data['ctl12$FCPartThree$ctl01$ddlState']=	'Choose a State';
			$data['ctl12$FCPartThree$ctl01$txtPostalCode']=	'';
			$data['ctl12$FCPartThree$ctl01$ddlSpecialty']=	1;
			$data['ctl12$FCPartThree$ctl01$btnSearchDoctors']=	'Search';
			$data['ctl12$FCPartOne$ctl01$stateDropDownList']=$state;
			$data['ctl12$FCPartOne$ctl01$cityTextBox']=$city;
			$data['callbackForAutoSave_ParamField']='';
			$data['hfSaveAsDraft']='False';
			$data['__VIEWSTATEENCRYPTED']='';
		

			log::info("Perform Search");
			$html = $this->Post($posturl,$data);

			$searchForm = new HtmlParser($html );				
				
			// set to 100 listings per page
			/*
			log::info("Switch to 100 listings per page");
			$data =  $searchForm->loadViewState();
			$data['__EVENTTARGET'] = 'ctl12$FCPartThree$ctl01$ddlResultsPerPageTop';
			$data['__EVENTARGUMENT'] = '';
			$data['__LASTFOCUS'] = '';
			$data['hdn_machinename'] = 'WEBPROD';
			$data['ctl12$ucFCHeader$txtSearch'] = 'Search';
			$data['ctl12$FCPartThree$ctl01$ddlResultsPerPageTop'] = '100';
			$data['ctl12$FCPartThree$ctl01$ddlResultsPerPageBottom'] = '20';
			$data['ctl12$FCPartOne$ctl01$stateDropDownList'] = '';
			$data['ctl12$FCPartOne$ctl01$cityTextBox'] = '';
			$data['callbackForAutoSave_ParamField'] = '';
			$data['hfSaveAsDraft'] = 'False';
			$data['__VIEWSTATEENCRYPTED'] = '';

			$this->LoadPostUrl("$posturl?f=Switch_To_100",$data);		*/
			$this->queuedFetch();
			$this->parseData();
		}
	}
	
	static $pageSeen = array();

	static function parse($url,$html)
//	static function loadCallBack($url,$html)
	{


		$posturl = "http://www.aad.org/find-a-derm";
		$query = array();
		

		$type = get_class();		
		$thiz = self::getInstance();		
		$host = parse_url($url,PHP_URL_HOST);
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$ohp =new Operating_Hours_Parser();

		$currentPageName = $query['f'];

		$page = new Xpath($html);		

		
		$data = $page->loadViewState();
		$data['hdn_machinename'] = 'WEBPROD';
		$data['ctl12$ucFCHeader$txtSearch'] = 'Search';
		$data['ctl12$FCPartThree$ctl01$ddlResultsPerPageTop'] = '100';
		$data['ctl12$FCPartThree$ctl01$ddlResultsPerPageBottom'] = '20';
		$data['ctl12$FCPartOne$ctl01$stateDropDownList'] = '';
		$data['ctl12$FCPartOne$ctl01$cityTextBox'] = '';
		$data['callbackForAutoSave_ParamField'] = '';
		$data['hfSaveAsDraft'] = 'False';
		$data['__VIEWSTATEENCRYPTED'] = '';
		
		// listings page
		
		$webRequests = array();
		foreach($page->query("//a[contains(@id,'gvResults')]") as $node)
		{
			list($name ,$junk) = explode("  ", $node->textContent);
			// grab the urls from the listing
			$clickEvent = $page->getClickEvent($node->getAttribute("href"));
			$target = $clickEvent["__EVENTTARGET"];

			$data = array_merge($data, $page->getClickEvent($node->getAttribute("href")));
			$webRequests[] = new WebRequest($posturl."?f=$target&cp=$currentPageName",$type,"POST", $data);
		}

		// load next page links
		foreach($page->query("//a[contains(@href,'Page$')]") as $node)
		{
			$pageNumber = $node->textContent;				
			$clickEvent = $page->getClickEvent($node->getAttribute("href"));
			list($junk, $pageNumber) = explode('$',$clickEvent["__EVENTARGUMENT"]);

			// grab the urls from the listing
			$data = array_merge($data, $clickEvent);
			$webRequests[] = new WebRequest($posturl."?f=$pageNumber",$type,"POST", $data);
		}

		if (sizeof($webRequests)>0)
		{
			$thiz->loadWebRequests($webRequests);
			return;
		}
	
		// data page
		$data = array();
		foreach($page->query("//span[contains(@id,'DoctorName_DOCTOR_NAMELabel')]") as $node)
		{
			$data['Doctor Name'] = $practiceName = $node->textContent;
		}

		foreach($page->query("//a[contains(@id,'DoctorName_HyperLink2')]") as $node)
		{
			$data['AAD Membership'] = $node->textContent;
		}

		//load the specialties gridview
		foreach($page->query("//table[contains(@id,'gvSpecialties')]//td") as $node)
		{
			$data['Specialties'] = $node->textContent;
		}

		foreach($page->query("//h3[text() = 'Doctor Contact Information']/following-sibling::div") as $node)
		{
			$contactArray = explode("|", strip_tags(str_replace("</div>","|", $node->c14n())));

			for( $offset=0; ($offset+9)<sizeof($contactArray); $offset++)
			{
				if ($offset%9==0)
				{
					// new office every nine lines
					if (!empty($contactArray[$offset]))
						$practiceName = $contactArray[$offset];// name in position zero

					$phone = $contactArray[$offset+6];
					$address  = join(" , ", array($contactArray[2+$offset],$contactArray[4+$offset],$contactArray[5+$offset]));
									
					// massage data for csv format.				
					for($k=1;isset($data["Practice $k Name"]);$k++){};
					
					$data["Practice $k Name"] = $practiceName;
					$data["Practice $k Phone"] = $phone;

					foreach($ap->parse($address) as $key=>$val)
					{
						$data["Practice $k ".$key] = $val;
					}
				}
			}
			
			//$data = array_merge($data,$ap->parse($address));
			//$data = array_merge($data,$pp->parse($address));
		}

		foreach($page->query("//table[contains(@id,'FormViewProfile')]//div/div") as $node)
		{
			$profile = new Xpath($node);
			$key = $value = null;
			foreach($profile->query("//h3") as $keyNode)
			{
				$key = trim($keyNode->textContent);
			}

			foreach($profile->query("//p") as $valueNode)
			{
				$value = trim($valueNode->textContent);
			}

			if (! empty($key))
			{
				if ($key == 'Special Proprietary Notice and Disclaimer')
					continue;
				/*if ($key == 'Office Hours')
					$data = array_merge($data,$ohp->parse($value));*/
				else
					$data[$key] = $value;
			}
		}
		
		if (isset($data['Doctor Name']))
		{
			$data['SourceUrl'] = $url;
			log::info($data);
			db::store($type,$data,array('SourceUrl'));
		}
	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='aad' and parsed=1 ");
/*db::query("DROP TABLE aad ");
*/
$r= new aad();
$r->parseCommandLine();

