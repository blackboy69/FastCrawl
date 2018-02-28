<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class wella extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
	/*	
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	*/

		$sf = $this->Get("http://www.wella.com/en-US/salon-finder.aspx");
		$x = new HtmlParser($sf);
		$post = $x->LoadViewState($x);
		$sql = "SELECT distinct zip FROM geo.locations where pop > 20999";

		$post['__EVENTTARGET']='ctl00$mainContent$ctl01$lbSalonFinderSubmit';
		$post['ctl00$ctl06$find'] ="salon";
		$post['ctl00$ctl07$inputSearchBox'] = "Search anything  on the Wella website";
		$post['ctl00$mainContent$ctl01$salonCountry'] ="United,States";
		$post['ctl00$mainContent$ctl01$distance_type'] = "miles";
		$post['ctl00$mainContent$ctl01$radius'] = "50";
		$post['distanceType'] = "miles";
		$post['ctl00$mainContent$ctl01$ddlResultPerPageRequest']  = "50";
		$post['ctl00$mainContent$ctl01$salonName'] = "";
		$post['ctl00$ctl08$popups$popupLoginRedirUrl']  ="/home.aspx";
		$post['ctl00$ctl08$popups$popupLoginLoginUrl'] = "/home.aspx";
		
		Log::info("Loading....");

		
		foreach(db::OneCol($sql) as $i => $zip)
		{
			echo ".";
			$post['ctl00$mainContent$ctl01$salonAddress'] = $zip;		
			$reqs[] = new WebRequest("http://www.wella.com/en-US/salon-finder.aspx?nr=1#$zip",$type,"POST",$this->buildQuery($post,2));

			if ($i%100==0)
			{
				$this->loadWebRequests($reqs);
				$reqs=array();
			}
		} 
		$this->loadWebRequests($reqs);
		$reqs=array();
		// canadian
		$canadian = array('Toronto', 'Montreal', 'Calgary', 'Ottawa', 'Edmonton', 'Mississauga', 'Winnipeg', 'Vancouver', 'Brampton', 'Hamilton', 'Quebec City', 'Surrey', 'Laval', 'Halifax', 'London', 'Markham', 'Vaughan', 'Gatineau', 'Longueuil', 'Burnaby', 'Saskatoon', 'Kitchener', 'Windsor', 'Regina', 'Richmond', 'Richmond Hill', 'Oakville', 'Burlington', 'Greater Sudbury', 'Sherbrooke', 'Oshawa', 'Saguenay', 'Lévis', 'Barrie', 'Abbotsford', 'St. Catharines', 'Trois-Rivières', 'Cambridge', 'Coquitlam', 'Kingston', 'Whitby', 'Guelph', 'Kelowna', 'Saanich', 'Ajax', 'Thunder Bay', 'Terrebonne', 'St. Johns', 'Langley', 'Chatham-Kent', 'Delta', 'Waterloo', 'Cape Breton', 'Brantford', 'Strathcona County', 'Saint-Jean-sur-Richelieu', 'Red Deer', 'Pickering', 'Kamloops', 'Clarington', 'North Vancouver', 'Milton', 'Nanaimo', 'Lethbridge', 'Niagara Falls', 'Repentigny', 'Victoria', 'Newmarket', 'Brossard', 'Peterborough', 'Chilliwack', 'Maple Ridge', 'Sault Ste. Marie', 'Kawartha Lakes', 'Sarnia', 'Prince George', 'Drummondville', 'Saint John', 'Moncton', 'Saint-Jérôme', 'New Westminster', 'Wood Buffalo', 'Granby', 'Norfolk County', 'St. Albert', 'Medicine Hat', 'Caledon', 'Halton Hills', 'Port Coquitlam', 'Fredericton', 'Grande Prairie', 'North Bay', 'Blainville', 'Saint-Hyacinthe', 'Aurora', 'Welland', 'Shawinigan', 'Dollard-des-Ormeaux', 'Belleville', 'North Vancouver');
		foreach ($canadian as $city)
		{
			echo ".";
			$post['ctl00$mainContent$ctl01$salonCountry'] ="Canada";
			$post['ctl00$mainContent$ctl01$salonAddress'] = $city;		
			$reqs[] = new WebRequest("http://www.wella.com/en-US/salon-finder.aspx?nr=1#".urlencode("$city, Canada"),$type,"POST",$this->buildQuery($post,2));
		}
		$this->loadWebRequests($reqs);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$data = array();

		if (preg_match("/Databases = \[(.*)}\];/s",$html,$matches ))
		{
			$json = $matches[1];

			$kvp = new KeyValue_Parser();
			foreach (explode("},{",$json) as $j)
			{
				$data = $kvp->parse($j);				
				$data['LNG'] = preg_replace("/'|}|,/","",$data['LNG']);
				unset($data['LATLNG']);

				if (!empty($data))
				{
					log::info($data);		
					db::store($type,$data,array('SALON','PHONE_NUMBER','CITY'));	
				}
			}
		}
		else
		{
			log::error("Not Found");
			log::error($url);
		}
	}

}

$r= new wella();
$r->parseCommandLine();

