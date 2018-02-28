<?
include_once "config.inc";

class chirohealth extends baseScrape
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
		
//		unlink ($this->cookie_file);
	//	$this->useCookies=true;
		//$this->login();
		//$this->loadUrl("https://www.chirohealth.org/member/findadentist_results.aspx?fragmentid=166&lastname=&city=&withinmiles=0&zipcode=01230&practicetype=AllSpecialties&foreignlanguage=AllLanguages");

		#$this->loadUrl("https://www.chirohealth.org/member/findadentist_details.aspx?dentistid=30885&city=Great%20Barrington&fragmentid=166");

		$url = "http://www.chirohealth.org/searchchiro.aspx";
		$html = $this->GET($url);
		$data = self::loadViewState($html);
		$data['__EVENTTARGET'] = 'ctl00$mainContent$SearchChiro1$lbtnFirst';
		$data['ctl00$SearchText'] = 'Search';

		self::parse($url, $this->Post("$url?".$this->buildQuery($data)));

   }
	
	// this should be in a parent class called baseScrapeAspx that has code for aspx sites.
	private static function loadViewState($html)
	{
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		
		
		$data = array();

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
		
		return $data;
		/*		
		// set the following variables
		$data['ctl00$MainContentArea$LoginBox'] = $user;
		$data['ctl00$MainContentArea$PasswordBox'] = $pass;
		$data['ctl00$MainContentArea$LoginButton'] = "Login";
		
		$this->Post("https://www.chirohealth.org/login.aspx?".$this->buildQuery($data));
		*/
	}


	static function parse($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		

		
		foreach ($x->query("//table[@id='ctl00_mainContent_SearchChiro1_rptItems']/tr") as $listing)
		{
			
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($listing->c14n());
			$x2 = new DOMXPath($dom2);	
			
			$data = array();
			foreach($x2->query("//span") as $node)
			{
				// parse each field
				list($key,$value) = explode(":",$node->textContent);

				$data[self::removeSpaces($key)] = $value;
			}

			if (sizeof($data) > 0)
			{
				log::info($data['FullName']);
				db::insertInto($type,$data,false,true);	
			}
		}



		// grab the next page
		foreach ($x->query("//a[@id='ctl00_mainContent_SearchChiro1_lbtnNext']") as $node)
		{
			if ($node->getAttribute("href"))
			{
				$t = self::getInstance();
				$url = "http://www.chirohealth.org/searchchiro.aspx";
				$data = self::loadViewState($html);
				$data['__EVENTTARGET'] = 'ctl00$mainContent$SearchChiro1$lbtnNext';
				$data['ctl00$SearchText'] = 'Search';

				self::parse($url, $t->Post("$url?".$t->buildQuery($data)));
			}
			break;
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
}
$r = new chirohealth();
$r->runLoader();
$r->parseData();
$r->generateCSV();
