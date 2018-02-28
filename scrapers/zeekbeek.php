<?
include_once "config.inc";
R::freeze(false);
		
class zeekbeek extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->threads=1;		
		
		
		// i know there are 930 page as of 7/9/2017. lets search for 1000 to handle future growth if we run this again.
		
		for($i=1;$i<1000;$i++)
		{
			$postUrl = "https://www.zeekbeek.com/DesktopModules/CloudLaw.WebServices/API/Solr/SearchMembers#$i";
			$data = '{"profession":"Lawyers","region":"IL","page":'.$i.',"url":"https://www.zeekbeek.com/ISBA/Search-Results#profession=Lawyers&region=IL","professionSetting":"Lawyer","vinclude":"ILSB","mexclude":"","sort":"geo"}';
			$this->loadWebRequest (new WebRequest($postUrl,$type,"POST", $data));
		}
		
		$this->headers[] = "Content-Type: application/json; charset=UTF-8";		
		$this->postContentType = "Content-Type: application/json";		
		
		$this->noProxy=FALSE;		
		#$this->proxy= "localhost:8888";
		$this->switchProxy();
		
		// prevent stupid errors yo
		//db::store($type,array(),array());	
		
		#db::query("update raw_data set parsed=0 where parsed=1 and url='https://www.zeekbeek.com/vcard.ashx?userId=143262' and type='zeekbeek'");
		
		// add this cookie __RequestVerificationToken to help you can get it from the UI by paging untill robot check is there
		//$this->useCookies = true;
		//$this->cookieData = 'visid_incap_374624=3gAtznj9TyOm8H3fSrCWMHVIXlkAAAAAQUIPAAAAAAAnWlcYkhsD8G4sEio/0GeS; zb-current-search=https://www.zeekbeek.com/ISBA; zb-current-location=IL; __RequestVerificationToken=ljb4hntYyKSVr3aIdeUlWBnnHfAp02WiYQCMKBlqn3y5bjyz50yZrOSuIgOXhykugttqIGBZFLAidh8W5EdgjPFJm9g0TqCa2npFI6WMT-Gsmeszlqu43MPSNFs1; ASP.NET_SessionId=lre1kqphjlgfkoyppuocc3l2; .ASPXANONYMOUS=qpu6n_Us0wEkAAAAZjgzYmNmYWQtZDg5MC00MWFmLWJiMmUtZjFjNWFmMmJjODAy0; incap_ses_32_374624=9gx5STeHFl+/W2TiMbFxAHNrYlkAAAAAmFJRYPFy62uGhAcFa9RN4Q==; _ga=GA1.2.2034552502.1499351162; _gid=GA1.2.192019382.1499617045; _gat=1; _gat_ILSBTracker=1; linkedin_oauth_75ckz69jrb4t16=null; linkedin_oauth_75ckz69jrb4t16_crc=null; _bizo_bzid=b82b7ff6-7a27-4cf5-85f8-86ef524ea809; _bizo_cksm=5357C29867E37953; _bizo_np_stats=155%3D245%2C; __zlcmid=hNgZgtHOUbVLHH; dnn_IsMobile=False; language=en-US';
	 	
		
		
		#db::query("update load_queue set processing=0 where type='$type' and url like 'https://www.zeekbeek.com/vcard.ashx%' ");
		#db::query("update load_queue set processing=1 where type='$type' and url in (select url from raw_data where type ='zeekbeek' and html like '%BEGIN:VCARD%'  and url like 'https://www.zeekbeek.com/vcard.ashx%')  ");
		#db::query("delete from raw_data where type ='zeekbeek' and html not like '%BEGIN:VCARD%'  and url like 'https://www.zeekbeek.com/vcard.ashx%'");
		
	
	}

	static function loadCallBack($url,$html,$arg3)
	{
	
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		$host = parse_url($url,PHP_URL_HOST);

		
		if (preg_match("#Incapsula#", $html))
		{			
			log::info($url);
			log::info("Incapsula protection hit");					
			$html=null;
			//sleep(300);
		}
		baseScrape::loadCallBack($url,$html,$arg3);		
		log::info("Sleeping 10 seconds");
		sleep(10);
	}
	
	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		$ap = new Address_Parser();
		$op = new Operating_Hours_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		
		$urls= array();
		if (preg_match("#DesktopModules/CloudLaw.WebServices/API/Solr/SearchMembers#", $url))
		{
			$json = json_decode ($html,true);
			//log::info($json);	
			
			foreach($json["results"] as $results)
			{
				$userId = $results["userId"];
				$urls[]= "https://www.zeekbeek.com/vcard.ashx?userId=$userId";
			}
			$thiz->loadUrlsByArray($urls);
		} 
		else
		{
			$vc = new Vcard(false,$html,  array('Collapse' => true));
			
			
			// 'FN', 'TITLE', 'ORG', 'TEL', 'EMAIL', 'URL', 'ADR', 'BDAY', 'NOTE'
			

			$data=array();
			#company name
			$data['COMPANY_NAME'] = @@strval($vc -> ORG['Name']);
			$data['TITLE'] = @@strval($vc -> ROLE);
			
			# name
			$data = array_merge($data, $np->parse($vc->fn));
			
			//address
			$address = @join(", ", @$vc->adr);
			$data = @array_merge($data, $ap->parse($address));
			
			
			$data["PHONE"] = @@strval($vc->tel ['Value']);	
				
			foreach($vc->tel as $tel)
			{
				$telType="";
				$telType = @strval($tel['Type'][1]);
				
				$data["PHONE_".$telType] = @strval($tel['Value']);
				
			}
			
			
			$data["EMAIL"] = @@strval($vc->email['Value']);				
						
			foreach($vc->url as $vcurl)
			{			
				if (!preg_match("#zeekbeek.com#",@strval($vcurl)))
					$data["WEBSITE"] = @strval($vcurl);
				else
					$data["ALT_SOURCE_URL"] = @strval($vcurl);
			} 

			$data['NOTE']= @strval($vc->note['Value']);			
			$data["SOURCE_URL"] = $url;
			
			foreach($data as $k=>$v)
			{
				if (empty($data[$k]))
					unset($data[$k]);
			}
			
			log::info($data);	
			log::info($vc);
			
			db::store($type,$data,array('SOURCE_URL'));	
		}
	
	}
}



$r= new zeekbeek();



$r->parseCommandLine();

