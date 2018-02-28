<?
include_once "config.inc";

class allscripts extends baseScrape
{
    public static $_this=null;
	public static $google = null;
	public static $bing = null;	
	public static $csv=null;
	public static $csvMap=array();

	private $urlsToLoad = array();
	
   public function runLoader()
   {
		
		$type = get_class();		

		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		
*/
		
		$this->proxy = "localhost:8888";		
		//$this->noProxy=true;

		$this->threads=2;
		$this->useCookies = true;
		$this->timeout = 8;
		$urlsToLoad = array();
		$urls[] = array();

		for($i=1;$i<29581;$i+=12)
		{
			$urls[] = "https://clientconnect.allscripts.com/people?sort=creationDate&showDisabledUsers=true&showExternalUsers=false&resultTypes=COMMUNITY&view=&start=$i&prefix=&tag=&query=&onlineOnly=false&recentlyAddedOnly=false&searchFilters%5B0%5D.fieldID=2&searchFilters%5B0%5D.value=";
		}

		$this->loadUrlsByArray($urls);
	
		$this->login();
		$this->queuedGet();
	}

	function login()
	{

		$this->useCookies=true;
		$loginUrl = "https://clientconnect.allscripts.com/cs_login";

		$loginForm['username'] = 'johnnys';
		$loginForm['password'] = 'test123';

		log::info("login: johnnys");
		$response = $this->Post($loginUrl,$this->buildQuery($loginForm));
		
		if (preg_match("/Invalid username or password. Please try again./",$response))
		{
			log::error("Invalid username/password!");
		}

	}
	
	static function loadCallBack($url,$html,$arg3)
	{
		$t = self::getInstance();
		$type = get_class();	
		$host = parse_url($url,PHP_URL_HOST);
		if (preg_match("/Log in using the form below./",$html))
		{
			$t->login();
		}
		else
		{
			if (strlen($html) < 1000)$html="";

			baseScrape::loadCallBack($url,$html,$arg3);
		}
	}
	
	static function parse($url,$html)
	{
		$t=self::getInstance();
		$type = get_class();	

		if (preg_match("/sort=creationDate/",$url))
		{
			$urls = array();
			$x = new Xpath($html);
			foreach($x->query("//a[contains(@class,'jive-username-link')]") as $node)
			{
				$urls[]= self::relative2Absolute($url,$node->getAttribute("href"));
			}
			$t->loadUrlsByArray($urls);
		}
		else
		{
			db::query("UPDATE raw_data set parsed=0 where url = '$url' and type='$type'");
			
			$x = new Xpath($html);
			foreach($x->query("//dl[@class='vcard']|//dl[contains(@class,'jive-profile-extras')]") as $node)
			{
				$vcard=array();
				foreach(explode("  ", $node->textContent) as $item)
				{
					if (trim($item) != "")
					{
						$vcard[] = $item;
					}
				}
				for($i=0;$i<sizeof($vcard);$i++)
				{
					if (preg_match("/:/",$vcard[$i]))
					{
						list($k,$junk) = explode(":", $vcard[$i]);
						
						$data[trim($k)] = trim($vcard[++$i]);
					}
				}
			}
			$data['Url'] = $url;
			if (isset($data['Name']))
			{			
				log::info($data);
				try {				
					db::store($type,$data,array('Name', 'Url'));
				}
				catch(Exception $e)
				{
					log::error ("Cannot store ".$data['Name']);
					log::error($e);
					//print_r($data);
					exit;
				}		
			}
		}
	}
}
$r = new allscripts();
$r->parseCommandLine();
