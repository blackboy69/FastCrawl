<?
include_once "config.inc";
R::freeze(false);
		
class icsc_org extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
	    
		$type = get_class();		
		
		//log::info("This script cannot be run multi threaded. Best to run one 'all' and one 'parse' make sure to cleanAll the first time.");
		//db::query(" ");
		
		$this->threads=1;		
		//$this->timeout = 90;
		//$this->debug=1;// http://network.icsc_org.org/network/members/profile/?UserKey=490eb84e-d245-4ff2-897d-f6aec1278f2f
		
		// and url$this->noProxy=false;
		$this->proxy = "localhost:8888";
		$this->noProxy=false;
		
		$this->useCookies=true;
		$thiz = self::getInstance();
		$loggedIn = $this->login();
		
		if ($loggedIn )
		{
			log::info("Login OK!");			
			$this->loadUrl("https://www.icsc.org/member/profile/220408/");
			return;
			
			foreach (range('A', 'Z') as $c1)
			{
				foreach (range('A', 'Z') as $c2)
				{
					$webRequests = array();
					$namekey = "$c1$c2";
					
					$filter = base64_encode('a:5:{s:5:"limit";i:100;s:14:"show-companies";s:2:"no";s:5:"alpha";s:0:"";s:8:"order_by";s:9:"last_name";s:10:"first-name";s:2:"'.$namekey.'";}');
					$url = "https://www.icsc.org/directories/members?filter=".$filter;					
					$thiz->loadUrl($url);		
					
					return;
				}
			}
		} else {
			log::info("Login failed!");
		}
	
	}
	

	static function loadCallBack($url,$html,$arg3)
	{
		$type = get_class();		
		$host = parse_url($url,PHP_URL_HOST);
		$thiz = self::getInstance();
		file_put_contents("lastfetch1.html",$html);
		if (!preg_match("#_partials/display-email#",$url))
		{
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
		}
		//sleep(2);
		
		baseScrape::loadCallBack($url,$html,$arg3);
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
		$data= array();
		
		if (preg_match("#/directories/members#",$url))
		{
			// load listings
			$links = array();
			foreach($x->query("//a[contains(@href, '/member/profile/')]") as $node)
			{
			  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			
			// get next page links
			foreach($x->query("//div[@class='pagination']//a']") as $node)
			{
			  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			log::info($links);
			$thiz->loadUrlsByArray($links);
		}
		else if (preg_match("#_partials/display-email#",$url))
		{
			parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip
			$data=array();
			$data['MEMBER_ID'] = $query['member_id'];
			$data=  array_merge($data, $ep->parse($html));		
			
			log::info($data);
			db::store($type,$data,array('MEMBER_ID'));
		}
		else if (preg_match("#/member/profile/([0-9]+)#",$url,$matches))
		{
			$data=array();			
			$member_id = $matches[1];
			$data ["MEMBER_ID"]=$member_id ;
			
						
			foreach($x->query("//h2") as $node)
			{
				$data=  array_merge($data, $np->parse($node->textContent));		
				break;
			}
			
			
			foreach($x->query("//div[@class='main']//p") as $node)
			{
				// Line 1 is title
				// line 2 is company
				// line 3+ is address
				$textAr = explode("\n", $node->textContent);
				
				$data['TITLE'] = array_shift($textAr);
				$data['COMPANY'] = array_shift($textAr);
				
				$address = join(" , ", $textAr);
				$data= array_merge($data, $ap->parse($address));
				
				break;				
			}

			foreach($x->query("//div[@class='sidebar']//a[@class='display-email']") as $node)
			{
				$eeId = $node->getAttribute("data-xid");
				
				// need to set the special header X-EEXID
				// load urls to get email address
				$headers=array();
				$headers[] = "X-Requested-With:XMLHttpRequest";
				$headers[] = "X-EEXID:$eeId";
				$headers[] = "Referer:https://www.icsc.org/member/profile/$member_id/";
				$headers[] = "DNT:1";
				$headers[] = "Origin: https://www.icsc.org";
				$headers[] = "Accept:*/*";
				$headers[] = "Content-Type:application/x-www-form-urlencoded; charset=UTF-8";
				$headers[] = "Cookie: exp_tracker=".URLENCODE(serialize(array("member/profile/$member_id", "_partials/display-email")));
				
				$thiz->setCookies(array("exp_tracker"=> URLENCODE(serialize(array("member/profile/$member_id", "_partials/display-email","member/profile/$member_id", "_partials/display-email")))));				
				$webRequest = new WebRequest("https://www.icsc.org/_partials/display-email",$type,"POST", array("member_id"=>"$member_id"),100,$headers); 
				$data['EMAIL'] = $thiz->PostWebRequest($webRequest); // force this load at least
				break;
			}
			
			
			foreach($x->query("//div[@class='main']//p") as $node)
			{
				$data= array_merge($data, $pp->parse($node->textContent));					
			}
			foreach($x->query("//div[@class='main']//p//a[contains(@href,'http')]") as $node)
			{
				$data['WEBSITE'] =  $node->getAttribute("href");
			}
			
			$data['SOURCE_URL'] = $url;			
			if (!empty($data))
			{
				//$citystate = urlencode();
				log::info($data);
				db::store($type,$data,array('MEMBER_ID'));
			}
		}		
	}
   
		public static function needLogin($html)
	{
		$thiz = self::getInstance();
		
		
		if (!strpos($html,'Sign Out'))			
		{
			log::info("Needs login");
			return true;
		}
		return false;
	}
	
	function login()
	{
		// first get the login page
		// inital data		
		$url = "https://www.icsc.org/member/login";
		$loginHtml = $this->get($url);
		$loginForm = new HtmlParser($loginHtml);
		
		list($loginData,$junk) = $loginForm->getForm();		
		$loginData["username"] = '1612626';
		$loginData["password"] = 'patel1';
		
		log::info("Logging IN");
		log::info($loginData);
		$html = $this->post($url,$loginData);
		file_put_contents("lastfetch.html",$html);
		
		return !$this->needLogin($html);
	}
}



$r= new icsc_org();



$r->parseCommandLine();

