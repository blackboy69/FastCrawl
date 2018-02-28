<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class apma extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		//$this->noProxy=false;
		//$this->proxy = "localhost:8888";
		
		$this->threads=4;
		
		/*
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->debug=false;
		*/
		
		//
		//		
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		db::query("DROP TABLE $type");
/*
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		
			
	
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");		
	
*/	


		$this->loadUrl('http://www.apma.org/Directory/FindAPodiatrist.cfm?Compact=0&FirstName=&LastName=&City=&State=&Zip=&Country=United+States');
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	

		// Load next page links
		$urls = array();
		foreach($x->query("//div[@class='formatted_content']/p[2]/a") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		if (!empty($urls))
		$thiz->loadUrlsByArray($urls);	


		$ap = new Address_Parser();




		$xOuter = new XPath($html);
		foreach ($xOuter->query("//div[@class='formatted_content']/table/tr") as $nodeOuter)
		{
			$x = new XPath($nodeOuter);	

			$data = array();
			foreach ($x->query("//td[1]") as $node)
			{
				$nameAddress = explode('<br>', $node->c14n());

				$data['NAME'] = strip_tags(trim($nameAddress[0]));
				$data['PRACTICE_NAME'] = "";

				if (sizeof($nameAddress) == 6)
				{
					$data['PRACTICE_NAME'] = strip_tags(trim($nameAddress[1]));
				}
				
				$data['ZIP'] =$data['COUNTRY'] =$data['STATE'] =$data['CITY'] = $data['ADDRESS2'] = $data['ADDRESS'] = "";
				$data = array_merge($data,$ap->parse($nameAddress));
			}

			foreach ($x->query("//td[2]") as $node)
			{
				$phoneFaxWeb = explode('<br>', $node->c14n());
				$data['WEB'] = $data['FAX'] =$data['PHONE'] ="";
				foreach($phoneFaxWeb as $fragment)
				{
					$fragmentClean = strip_tags(trim($fragment));
					
					if (strpos($fragment, 'phone')>-1)
						$data['PHONE'] = str_ireplace("phone", "", $fragmentClean);					
					
					if (strpos($fragment, "fax")>-1)
						$data['FAX'] = str_ireplace("fax", "", $fragmentClean);
	
					if (strpos($fragment, "href")>-1)
						$data['WEB'] = $fragmentClean;
				}

			}

			foreach ($x->query("//td[3]") as $node)
			{
				$edQualCert = explode('<br>', $node->c14n());
				$data['EDUCATION_SCHOOL'] =$data['EDUCATION_YEAR'] =$data['CERTIFICATIONS'] = "";

				foreach($edQualCert as $fragment)
				{
					$fragment = strip_tags(trim($fragment));

					if (preg_match("/University|School|College|State/i",$fragment))
						$data['EDUCATION_SCHOOL'] = $fragment;			
					else if (preg_match("/[1-2][0-9][0-9][0-9]/",$fragment))
						$data['EDUCATION_YEAR'] = $fragment;			
					else
						$data['CERTIFICATIONS'] = $fragment;	
				}
			}
			
			if (!empty($data))
			{
				unset ($data['RAW_ADDRESS']);
				$data['SOURCE_URL'] = $url;
				log::info($data['NAME']);		
				db::store($type,$data,array('NAME','PRACTICE_NAME','CITY','PHONE','ZIP'));	
			}					
		}
	}
}

$r= new apma();
$r->parseCommandLine();

