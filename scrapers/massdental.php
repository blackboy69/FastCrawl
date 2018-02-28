<?
include_once "config.inc";

class massdental extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		#db::query("DELETE FROM load_queue where type='$type' ");
		#db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		#db::query("DELETE FROM raw_data where type='$type' ");
		#db::query("DELETE FROM $type");

		
		$this->threads=1;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
		unlink ($this->cookie_file);
		$this->useCookies=true;
		$this->login();
		//$this->loadUrl("https://www.massdental.org/member/findadentist_results.aspx?fragmentid=166&lastname=&city=&withinmiles=0&zipcode=01230&practicetype=AllSpecialties&foreignlanguage=AllLanguages");

		#$this->loadUrl("https://www.massdental.org/member/findadentist_details.aspx?dentistid=30885&city=Great%20Barrington&fragmentid=166");

		$this->loadUrlsByStateZip("https://www.massdental.org/member/findadentist_results.aspx?fragmentid=166&lastname=&city=&withinmiles=0&zipcode=%ZIP%&practicetype=AllSpecialties&foreignlanguage=AllLanguages",'MA');
   }
	
	private function login($user='kmarshfamily4@verizon.net',$pass='drken1222')
	{
log::info("Logging in");
		$loginUrl = "https://www.massdental.org/login.aspx";

		$html = $this->Get($loginUrl);

		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		
		

		// grab categories from subnavigation
		foreach($x->query("//*[@name='__VIEWSTATE']") as $node)
		{
			$data['__VIEWSTATE'] = $node->getAttribute('value');
			break;
		}

		foreach($x->query("//*[@name='__EVENTVALIDATION']") as $node)
		{
			$data['__EVENTVALIDATION'] = $node->getAttribute('value');
			break;
		}
		
		// set the following variables
		$data['ctl00$MainContentArea$LoginBox'] = $user;
		$data['ctl00$MainContentArea$PasswordBox'] = $pass;
		$data['ctl00$MainContentArea$LoginButton'] = "Login";
		
		$this->Post("https://www.massdental.org/login.aspx?".$this->buildQuery($data));
	}

	static function parseListings($url,$html)
	{
		// parse the listsings of jobs page. 
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		
		
		foreach($x->query("//a") as $node)
		{

			if (preg_match("/findadentist_details.aspx/", $node->getAttribute("href")))
			{
				$href = preg_replace("/ /","+",self::relative2absolute($url, $node->getAttribute("href")));
				self::getInstance()->loadUrl($href);
			}
		}		
	}


	static function parseDetails($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		
		$data['state'] = 'MA';

		foreach($x->query("//div[@class='dentistDetails']/h3") as $node)
		{
			$data['name'] = self::cleanup($node->textContent);	
		}

		foreach($x->query("//div[@class='dentistDetails']/h4") as $node)
		{
			$data['type'] = self::cleanup($node->textContent);	
		}

		foreach($x->query("//div[@class='dentistDetails']/p") as $node)
		{
			$data['notes'] = self::cleanup($node->textContent);	
		}



		
		$addressRaw = array();
		$address = array();
		foreach($x->query("//div[@id='ctl00_MainContentArea_addressPanel']/p[3]") as $node)
		{
			$address = explode("<br>",$node->c14n());
		}

		$i = 0;
		foreach ($address as $k=>$v)
		{
			$v = $address[$k] = self::cleanup(strip_tags($v));

			if ($i == 0)
			{
				$data['address'] = $v;
			}
					
			else if (preg_match("/@/",$v))
			{
				//is it an email address?
				$data['email'] = $v;
			}
			else if (preg_match("/:/",$v))
			{
				list($key,$value) = explode(":",$v);
				$data[strtolower(trim($key))] = trim($value);
			}		
			else
			{
				// just append to the street address.

				$data['address'] .= " ".$v;
			}
			$i++;				
		}
		print_r($data);
		
		db::insertInto($type,$data,false,true);	

	}

	static function parse($url,$html)
	{	
		
		$type = get_class();

		if (empty($url))
		{
			return;
		}

		if (preg_match("/findadentist_results.aspx/",$url))
		{
			return self::parseListings($url,$html);
		}
		else if (preg_match("/findadentist_details.aspx/",$url))
		{
			return self::parseDetails($url,$html);
		}
		else
		{
			log::info("Unknown Url $url");		
		}
	}


	static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/","",$str);
	}
	
	static function cleanup($str)
	{
		while (preg_match("/  /",$str))
		{
			$str = preg_replace("/  /i"," ",$str);
		}
		return trim($str);
	}

	protected function curlInit($url,$method)
	{

		# remove any trailing # signs, but don't take ? query params out
		$actualURL = preg_replace("/\#.+\???/","",$url);

		$ch = curl_init();
		
		if ($this->proxy != false)
		{
			#curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy); 
		}

		if ($this->useCookies)
		{
			// this will reset cookies on each invocation
			// curl_setopt($ch, CURLOPT_COOKIESESSION,1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); 

			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		}

		if ($this->cookieData)
		{
			curl_setopt($ch, CURLOPT_COOKIE, $this->cookieData);
		}
		
		$referer = $this->getReferer($url);
		if (!empty($referer) )
		{
			log::debug("Getting referrer for $url. GOT: ".$referer);
			curl_setopt($ch,CURLOPT_REFERER,$referer); 
		}

		if (strtoupper($method) == "POST")
		{
			if (! strstr($actualURL,'?'))
			{
				log::error("Please ADD post fields on the url string just as you would a get. This url has no post fields! $actualURL");
				return;
			}
			list($actualURL,$data) = explode('?',$actualURL);

			curl_setopt($ch, CURLOPT_POST, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
		}


		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->allowRedirects);// allow redirects 

		
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'readHeader'));
		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_URL, $actualURL);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers); 
		curl_setopt($ch, CURLOPT_ENCODING , "gzip"); 
	
		## Below two option will enable the HTTPS option.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);   

		// set this to true and run on the command line to see the debug output. it isn't really usefull
		if ($this->debug)
		{
			curl_setopt($ch, CURLOPT_VERBOSE, true); 
		}
		if (is_array($this->headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers); 
		}

		return $ch;
	}
}
$r = new massdental();
$r->runLoader();
$r->parseData();
$r->generateCSV();
