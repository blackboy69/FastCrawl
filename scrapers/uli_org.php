<?
include_once "config.inc";
R::freeze(false);
		
class uli_org extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
	    
		$type = get_class();		
		
		log::info("This script cannot be run multi threaded. Best to run one 'all' and one 'parse' make sure to cleanAll the first time.");
		//db::query("DELETE FROM load_queue where type ='$type' and processing = 0");
		
		$this->threads=1;		
		//$this->timeout = 90;
		//$this->debug=1;
		$this->useCookies=true;
		$thiz = self::getInstance();
		$loggedIn = $this->login();
		
		if ($loggedIn )
		{
			log::info("Login OK!");
			
			$webRequests = array();
			$result = mysql_query("SELECT CITY, STATE FROM geo.US_CITIES order by pop desc LIMIT 1000");      
			while ($r = mysql_fetch_row($result))
			{
				$city = $r[0];
				$state = $r[1];
				$citystate = urlencode("$city$state");
				$url = "https://netforum.uli.org/eweb/DynamicPage.aspx?site=ULI2015&webcode=IndDirectory&citystate=".$citystate;
				
				$postData ="__APPLICATIONPATH=%2Feweb&__VIEWSTATE=%2FwEPDwUJNzgxNDUxMTA3ZGQt%2BDdUG%2F8OLK58EOnldL7l2Sr1aCktQjbzL%2FrXY%2FfiJA%3D%3D&__VIEWSTATEGENERATOR=BC7B2B63&C_2_1%24ValueTextBox0=&C_2_1%24ValueTextBox1=&C_2_1%24ValueTextBox2=&C_2_1%24ValueTextBox3=" . urlencode($city) . "&C_2_1%24ValueDropDownList4=" . urlencode($state)."&C_2_1%24ValueDropDownList5=&C_2_1%24ValueDropDownList6=&C_2_1%24ValueDropDownList7=&C_2_1%24ValueDropDownList8=&C_2_1%24ButtonFindGo=Search";				
				
				$webRequest = new WebRequest($url,$type,"POST", $postData,$priority=500);  // loading these with a low priority means each pages children are parsed before moving on to start a new search.
				
				log::info("");
				log::info("");
				log::info("Loading: $city $state");
				log::info("");
		
						
		
				//$html = $thiz->loadWebRequest($webRequest,true); // force this load at least
				$html = $thiz->PostWebRequest($webRequest);
				if ($thiz->needLogin($html))
					$html = $thiz->PostWebRequest($webRequest);
				
				file_put_contents("lastfetch.html",$html);
				
				$thiz->parse($url, $html);
				
				$thiz->queuedFetch();
				//$thiz->parseData();
				
				
			}
			
			
		} else{
			log::info("Login failed!");
		}
	
	}
	

	static function loadCallBack($url,$html,$arg3)
	{
		$type = get_class();	
		$host = parse_url($url,PHP_URL_HOST);
		$thiz = self::getInstance();
		if ($thiz->needLogin($html))
		{
			$thiz->login();
			return;
		}
		if (strlen($html) < 3000)
		{
			log::error("GOT tiny html?");
			log::info($html);
			
			return;
		}
		//sleep(2);
		
		baseScrape::loadCallBack($url,$html,$arg3);
	}
	
	
	public static function needLogin($html)
	{
		$thiz = self::getInstance();
		if (preg_match("/Login Required/",substr($html,1000)))			
		{
			log::info("Needs login");
			return true;
		}
		return false;
	}
	
	public static function parse($url,$html)
	{
		$type = get_class();    
		$thiz = self::getInstance();

		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$np = new Name_Parser();
		$kvp = new KeyValue_Parser(); 

		log::info($url);

		$webRequests = array();
		  
		$x = new HtmlParser($html);		
	
		foreach($x->query("//div[@class='search-result']") as $nodeTop)
		{
			$xInner = new HtmlParser($nodeTop->c14n());
			
			$data= array();
			
			foreach($xInner->query("//div[@class='span3'][1]/div[1]") as $node)
			{
				$data=  array_merge($data, $np->parse($node->textContent));		
			}
			foreach($xInner->query("//div[@class='span3'][1]/div[2]") as $node)
			{
				$data['TITLE'] = $node->textContent;		
			}			
			
			foreach($xInner->query("//div[@class='span3'][1]/div[3]") as $node)
			{
				$data['COMPANY'] = $node->textContent;		
			}	

			//email
			foreach($xInner->query("//div[contains(@class,'search-results-email')]") as $node)
			{
				$data = array_merge($data, $ep->parse($node->textContent));		
			}
			
			//phone
			foreach($xInner->query("//div[contains(@class,'search-results-phone')]") as $node)
			{
				$data['PHONE'] = $node->textContent;		
			}			

			foreach($xInner->query("//div[contains(@class,'search-results-fax')]") as $node)
			{
				$data['FAX'] = $node->textContent;		
			}			

			$address = "";
			//address
			foreach($xInner->query("//span[@class='search-results-address']//*") as $node)
			{
				$address .=  self::cleanup($node->textContent).", ";
			}
			$data = array_merge($data, $ap->parse($address));		
			

			$m1=$m2=$m3="";
			foreach($xInner->query("//div[@class='span3'][3]/div[1]") as $node)
			{
				$m1 = $node->textContent;		
			}
			foreach($xInner->query("//div[@class='span3'][3]/div[2]") as $node)
			{
				$m2 = $node->textContent;		
			}			
			foreach($xInner->query("//div[@class='span3'][3]/div[3]") as $node)
			{
				$m3 = $node->textContent;		
			}	
			
			$data['MEMBERSHIP_CHAPTER'] = $m1;
			if (!empty($m3))
			{
				$data['MEMBERSHIP_SUBCHAPTER'] = $m2;
				$data['MEMBERSHIP_LEVEL'] = $m3;				
			}
			else
			{
				$data['MEMBERSHIP_LEVEL'] = $m2;				
			}
			
			if (!empty($data))
			{
				//$citystate = urlencode();
				log::info($data['FIRST_NAME']. " ".$data['LAST_NAME']." ".$data['CITY']." " .$data['STATE']);
				db::store($type,$data,array('LAST_NAME', 'EMAIL', 'PHONE'));
			}
		}
		
		if (!empty($data))
		{
			
			//$citystate = urlencode($data['CITY'].$data['STATE']);
			
			// load next pages`
			$formDatas = $x->getForm();		
			$formData=$formDatas[0];
			
			parse_str($formData['action'],$qs2);
			$wk = $qs2['WebKey'];
			
			//log::info($wk);
				
			parse_str(parse_url($url,PHP_URL_QUERY),$qs);
			$citystate = urlencode($qs['citystate']);
			if (empty($citystate)) { 
				
				log::error("Bogus search. loading.");
				$city = $data['CITY'];
				$state = $data['STATE'];
				$citystate = urlencode("$city$state");				
			}
			
			foreach($x->query("//ul[@class='pagination']//a") as $node)
			{
				$pageNumber = $node->textContent;			
				if (preg_match("/current/",$pageNumber)) continue;
					
				$clickEvent = $x->getClickEvent($node->getAttribute("onclick"));
				
				// grab the urls from the listing		
				$formData = array_merge($formData, $clickEvent);
				$urlToPost =  self::relative2absolute($url, "/eweb/DynamicPage.aspx?Site=ULI2015&WebKey=$wk&FromSearchControl=Yes&pageNumber=$pageNumber&citystate=$citystate");
				
				unset($formData['action']);
				$webRequests[] = new WebRequest($urlToPost,$type,"POST", $formData);
			}
			

			if (sizeof($webRequests)>0)
			{
				$thiz->loadWebRequests($webRequests);
				//$thiz->queuedFetch(); // now process them before we parse anything else.
				
			}
		}
	  
	  

   }
	
	function login()
	{
		// first get the login page
		// inital data
		$loginData =array();
		$loginQs = "__APPLICATIONPATH=%2Feweb&__EVENTTARGET=eWebLoginControl%24LoginGoButton&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUJNzgxNDUxMTA3ZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAQUjZVdlYkxvZ2luQ29udHJvbCRDaGVja0JveFJlbWVtYmVyTWUxITrAHqa4FmFlB%2BXq85WazjWav5ABvCQzQmEDCqPGCA%3D%3D&__VIEWSTATEGENERATOR=BC7B2B63&eWebLoginControl%24TextBoxLoginName=anapolitano%40ten-x.com&eWebLoginControl%24TextBoxPassword=Navy1212&eWebLoginControl%24LoginGoHidden=";
		
		parse_str($loginQs,$loginData); 
		
		$url = "https://netforum.uli.org/eweb/DynamicPage.aspx?WebCode=LoginRequired&expires=yes&Site=ULI2015";
		$loginHtml = $this->get($url);
		//$loginForm = new HtmlParser($loginHtml);
		
		//overwrite view state
		//$loginData = array_merge($loginData, $loginForm->loadViewState());
		//$loginData["__EVENTTARGET"] = 'eWebLoginControl$LoginGoButton';

		//log::info($loginData);
		$html = $this->post($url,$loginQs);
		//file_put_contents("lastfetch.html",$html);
		//log::info($html);
		$x = new Xpath($html);	
		
		foreach($x->query("//h1[contains(., 'My Account')]") as $node)
		{
			return true;
		}
		return false;		
	}
/*
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
	}*/
	
}



$r= new uli_org();



$r->parseCommandLine();

