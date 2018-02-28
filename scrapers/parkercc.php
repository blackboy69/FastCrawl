<?
include_once "config.inc";

class parkercc extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		#db::query("DELETE FROM load_queue where type='$type' ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		#db::query("DELETE FROM raw_data where type='$type' ");
		#db::query("DELETE FROM $type");

		
		$this->threads=4;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
//		unlink ($this->cookie_file);
	//	$this->useCookies=true;
		//$this->login();
		//$this->loadUrl("https://www.parkercc.org/member/findadentist_results.aspx?fragmentid=166&lastname=&city=&withinmiles=0&zipcode=01230&practicetype=AllSpecialties&foreignlanguage=AllLanguages");
		
		for($i=1;$i<=336;$i++)
		{
			$this->loadUrl("http://www.parkercc.edu/DCReferrals.aspx?vSkip=true&page=$i");
		}
   }
	

	static function parse($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		
		foreach ($x->query("//div[@class='dcrefsearchresults']") as $listing)
		{
			
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($listing->c14n());
			$x2 = new DOMXPath($dom2);	
			
			$data = array();
			foreach($x2->query("//div[@class='dcrefname']") as $node)
			{
				$data['name'] = self::cleanup($node->textContent);
			}

			foreach($x2->query("//span[@class='dcrefcompany']") as $node)
			{
				$data['company'] = self::cleanup($node->textContent);
			}

			foreach($x2->query("//span[@class='dcrefaddress']") as $node)
			{
				$data['address'] = self::cleanup($node->textContent);
			}

			foreach($x2->query("//span[@class='dcrefphone']") as $node)
			{
				$data['phone'] = self::cleanup($node->textContent);
			}

			foreach($x2->query("//a") as $node)
			{
				$data['website'] = $node->getAttribute('href');
			}

			foreach($x2->query("//div[@class='dcreftechnique']") as $node)
			{
				$data['techniques'] = self::cleanup($node->textContent);
			}
			
			foreach($x2->query("//span[@class='dcrefsrupdate']") as $node)
			{
				$data['last_updated_on_site'] = self::cleanup($node->textContent);
			}

			log::info($data['name'] );
         db::replaceInto($type,$data);
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
$r = new parkercc();
$r->runLoader();
$r->parseData();
$r->generateCSV();
