<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class patientconnect365 extends baseScrape
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
		
			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	*/

		$this->loadUrlsByZip("https://www.patientconnect365.com/Dentist/SearchDentists?IdDentalNeed=0&IdLanguage=0&IdInsuranceAccepted=0&IdPaymentAccepted=0&ZipCode=%ZIP%");
		}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$data = array();
		$x = new HtmlParser($html);	
		$op = new Operating_Hours_Parser();
		$ap = new Address_Parser();
		$pp=new Phone_Parser();

		$urls = array();
		foreach ($x->query("//div[@class='logo']/a[@target='_blank']") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}

		$thiz->loadUrlsByArray($urls);

		foreach ($x->query("//div[contains(@id,'practiceDetails')]//div") as $node)
		{
			$data['NAME'] = self::cleanup($node->textContent);
			break;
		}

		foreach ($x->query("//div[contains(@id,'address')]") as $node)
		{
			$data = array_merge($data, $ap->parse($node->textContent));
		}

		foreach ($x->query("//div[contains(@id,'practiceDetails')]//div") as $node)
		{
			
			if (preg_match("/([A-Z]+) ([0-9]{3}-[0-9]{3}-[0-9]{4})/i",self::cleanup($node->textContent),$matches))
			{
				$data[$matches[1]] = $matches[2];
			}
			if (preg_match("/([A-Z]+) (.+[a-z0-9_\-]\.[a-z]+.+)/i",self::cleanup($node->textContent),$matches))
			{
				$data[$matches[1]] = $matches[2];
			}
		}

		
		$data['Rating']  = 0;
		foreach ($x->query("//div[contains(@id,'totalReviewRating')]//img[not(contains(@src,'EmptyStar'))]") as $node)
		{
			$data['Rating']++;
		}

		foreach ($x->query("//div[contains(@id,'totalReviewRating')]//a") as $node)
		{
			$data['Reviews'] = $node->textContent;
		}

		foreach ($x->query("//div[contains(@id,'practiceDetails')]//div") as $node)
		{
			$data['INFO'] = self::cleanup($node->textContent);
		}
		if (!empty($data['NAME']))
		{
			log::info($data);		
			db::store($type,$data,array('NAME','PHONE','ZIP'));	
		}
	

	}
}

$r= new patientconnect365();
$r->parseCommandLine();

