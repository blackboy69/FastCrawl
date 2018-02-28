<?
include_once "config.inc";
//R::freeze();

class naiglobal_com extends baseScrape
{
    public static $_this=null;
   public $postContentType = "Content-Type: text/xml";
   public function runLoader()
   {
		$type = get_class();		
	/*			
			$this->noProxy=false;
		$this->proxy = "localhost:8888";

			
			

			$this->useCookies=false;
			$this->allowRedirects = true;
			
			$this->debug=false;
		*/	
		//db::query("DELETE FROM LOAD_QUEUE WHERE type = '$type' and url like 'localhost%'");
		//db::query("DELETE FROM RAW_DATA WHERE type = '$type'");
		//$this->clean();
		$this->threads=1;		
		
		$urls = array(); //do a search by first name from a-z
		foreach(range('a','z') as $letter)
		{
			$urls[]=  "http://www.naiglobal.com/FindContact.ashx?fname=$letter&lname=&newPageIndex=1";
		}
		$this->loadUrlsByArray($urls);		
		
		//$this->loadUrl("http://www.naiglobal.com/agents/randall-b-boughton");
	}
	

	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		$x =  new  XPath($html);		
		
		if (preg_match("#FindContact.ashx#",$url))
        {			
			$urls = array();
			foreach($x->query("//a[contains(@href,'/agents/')]") as $node)
			{
				$urls[] = $node->getAttribute("href");
			}	
						
			// next page links
			foreach($x->query("//a[@class='pageNumLink']") as $node)
			{
				$letter = $query['fname'];
				$index = $node->textContent;
				$urls[]=  "http://www.naiglobal.com/FindContact.ashx?fname=$letter&lname=&newPageIndex=$index";
			}	


			if (sizeof($urls)>0)			
				$thiz->loadUrlsByArray($urls);		
        }
		else // parse person details page
		{
			$data = array();
				
			foreach ($x->query("//span[@id='dnn_lblFirstName']") as $node)
			{	
				$data['FIRST_NAME'] = $node->textContent;
			}
			
			foreach ($x->query("//span[@id='dnn_lblLastName']") as $node)
			{	
				$data['LAST_NAME'] = $node->textContent;
			}
			
			foreach ($x->query("//span[@id='dnn_lblTitle']") as $node)
			{	
				$data['TITLE'] = $node->textContent;
			}
						
			foreach ($x->query("//span[@id='dnn_lblFirmName']") as $node)
			{	
				$data['COMPANY_NAME'] = $node->textContent;
			}
			
			foreach ($x->query("//div[contains(@class, 'contactNumbersEmailContainer')]") as $node)
			{
				$data= array_merge($data,$ep->parse($node->textContent));
			}			
						
			foreach ($x->query("//span[@id='dnn_lblAddress']") as $node)
			{	
				$data['ADDRESS'] = $node->textContent;
			}
						
			foreach ($x->query("//span[@id='dnn_lblCityRegion']") as $node)
			{	
				$data['CITY'] = $node->textContent;
			}
						
			foreach ($x->query("//span[@id='dnn_lblStateProvince']") as $node)
			{	
				$data['STATE'] = $node->textContent;
			}
			
						
			foreach ($x->query("//span[@id='dnn_lblCountry']") as $node)
			{	
				$data['COUNTRY'] = $node->textContent;
			}
			
			foreach ($x->query("//span[@id='dnn_lblPostalCode']") as $node)
			{	
				$data['ZIP'] = $node->textContent;
			}

						
			foreach ($x->query("//span[@id='dnn_lblMobilePhone']") as $node)
			{	
				$data['MOBILE_PHONE'] = $node->textContent;
			}
			
			foreach ($x->query("//span[@id='dnn_lblMobilePhone']") as $node)
			{	
				$data['FAX'] = $node->textContent;
			}
			
			foreach ($x->query("//span[@id='dnn_lblDRELicenseNumber']") as $node)
			{	
				$data['LICENSE_NUMBER'] = $node->textContent;
			}	
			
			foreach ($x->query("//span[contains(@id,'agentprofile_lblSpecialtyList')]") as $node)
			{	
				$data['SPECIALTIES'] = $node->textContent;
			}				
			
			foreach ($x->query("//span[@class='personalWebsiteLink']//a") as $node)
			{	
				$data['WEBSITE'] = $node->getAttribute("href");
			}
			
			
			// now parse the free form text fields
			
			foreach ($x->query("//div[contains(@class,'eightcol')]") as $node)
			{	
				$key = null;
				$values = array();
				foreach(explode("\n", $node->c14n()) as $line)
				{
					$cleanLine = trim(strip_tags($line));
					// grab the key
					if(preg_match("#<strong>#",$line))
					{
						if (!empty($values) && !empty ($key))
							$data[$key]=join("\n", $values);
						
						$key = $cleanLine;			
						
						$values=array();
						$values[] = trim(str_replace($key,"",$cleanLine));
						
						// further process key 
						$key = preg_replace("#    .+#","",$key);
						$key = preg_replace("#&amp;#","and",$key);
					}		
					else
						$values[] = $cleanLine;						
					
					
					
				}
				if (!empty ($key))
					$data[$key]=join("\n", $values);// gets the last one
			}
			
			$data = db::normalize($data);
			$data["SOURCE URL"] = $url;
			log::info($data);			
			
			db::store($type,$data,array('FIRST_NAME','LAST_NAME','ADDRESS','CITY'),true);	

		}
					
	}
}

$r= new naiglobal_com();
$r->parseCommandLine();

