<?
include_once "config.inc";

class vitals extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
//		$this->proxy = "localhost:8888";

		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		$this->timeout=7;
	/*	
		db::query("UPDATE load_queue set processing = 0 where type='vitals' and processing = 1 ");		
		db::query("UPDATE raw_data set parsed = 0 where type='vitals' and parsed = 1  ");
		
				
				db::query("UPDATE raw_data set parsed = 0 where type='vitals' and parsed = 1  and url like 'http://www.vitals.com/doctors/Dr_% ");
				db::query("DELETE FROM LOAD_QUEUE where type='vitals' and url like 'http://www.vitals.com/doctors/Dr_%' ");


		db::query("DROP TABLE vitals");
		db::query("DELETE FROM raw_data where type='vitals'");			
		db::query("DELETE FROM load_queue where type='vitals'");

		db::query("DELETE FROM raw_data where type='vitals'");			
		db::query("DELETE FROM load_queue where type='vitals'");	*/

		unlink($this->cookie_file);
		for($i=97;$i<=122;$i++)
			$urls[] = "http://www.vitals.com/doctors/doctor-".chr($i).".html";

		$this->loadUrlsByArray($urls);
		$this->queuedFetch();
	}

	static function loadCallBack($url,$html,$arg3)
	{
		$thiz = self::getInstance();
		$type = get_class();	

//int_R($html);
		if (preg_match("/captcha/",$url) )
		{
			unlink($thiz->cookie_file);
			$thiz->get("http://www.vitals.com");
			self::getInstance()->switchProxy($url);
			return;
		}		
		baseScrape::loadCallBack($url,$html,$arg3);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$data = array();
		$kvp = new KeyValue_parser();
		$ap = new Address_Parser();
		$pp = new Phone_Parser();

		$urls = array();
		log::info($url);

		global $seen;
		if (!isset($seen)) $seen = array();

		foreach($x->query("//div[@id='nav2']//a") as $node)
		{
			$link =  $node->getAttribute('href');
			if (!isset($seen[$link]))
				$urls[] =$link;		
			$seen[$link]=true;
		}
		foreach($x->query("//a[contains(@title,'Profile of')]") as $node)
		{
			//$link =  preg_replace("/.html$/","/", $node->getAttribute('href'));

			if (!isset($seen[$link]))
				$urls[] =$link;		
			$seen[$link]=true;
		}
		if (sizeof($urls)>0)
		{
			$thiz->loadUrlsByArray($urls);		
			return;
		}


	
		foreach($x->query("//span[@class='p2_name']") as $node)
		{
			$data['NAME'] = self::cleanup($node->textContent);
		}

		foreach($x->query("//span[@class='p2_spec']") as $node)
		{
			$data['SPECIALITY'] = self::cleanup($node->textContent);
		}
		
		foreach($x->query("//span[@class='p2_genexp']") as $node)
		{
			$data['GENDER_EXPERIENCE'] = self::cleanup($node->textContent);
		}


		foreach($x->query("//div[@class='adr']//div") as $node)
		{
			$name = $node->getAttribute("class");
			$data[$name] = $node->textContent;
		}

		$addressHtml = "";
		$addressText = "";
		foreach($x->query("//table[@id='section_address']") as $node)
		{
			$addressHtml = $node->c14n();
			$addressText = $node->textContent;
		}

		list($phoneAddress1,$junk) = explode("Map this Address",$addressHtml);
		$data = array_merge($data,$pp->parse($phoneAddress1));
		$data = array_merge($data,$ap->parse($phoneAddress1));

		$data['RAW_ADDRESS'] = $addressText;
		$data['RAW_PHONE'] = $addressText;
		
		$addressHtml = "";
		foreach($x->query("//table[@id='section_address']//a[contains(text(),'additional addresses')]") as $node)
		{
			$data['ADDITIONAL_ADDRESSES'] = 'YES';
		}

		$afilliations = array();
		foreach($x->query("//a[contains(@onclick,'section_hospital')]") as $node)
		{
			$afilliations[] = self::cleanup($node->textContent);
		}
		$data['AFFILIATIONS'] = join(",",$afilliations);
		
		foreach($x->query("//div[@class='p2_stars_padding']//img") as $node)
		{
			$data['RATING'] = basename($node->getAttribute('src'));
		}



		$data = db::normalize($data);
		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;
			log::info($data);					
			db::store($type,$data,array('NAME','CITY','STATE','PHONE'),true);	
			return;
		}
		
		log::info("Not found: $url");
	}
}
$r= new vitals();
$r->parseCommandLine();

