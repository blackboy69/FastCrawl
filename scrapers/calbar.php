<?
include_once "config.inc";
//R::freeze();

class calbar extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->threads=1;		
		
		// to determine upper and lower limits,
		// do a search at http://members.calbar.ca.gov
		// sort by admission date, then see the largest numbers
		// this gives at least 10k headroom
		$urls = array();
		for ($i=315000;$i>20000;$i--)
		{
			$urls[] = "http://members.calbar.ca.gov/fal/Member/Detail/$i";	
			
			/*if ($i<305000)
				break;*/
		}		
		
		$this->loadUrlsByArray($urls);
	//db::query("UPDATE raw_data set parsed = 1 where type='$type' and parsed = 0  ");
		//$this->loadUrl("http://members.calbar.ca.gov/fal/Member/Detail/309895",true);
		//$this->loadUrl("http://members.calbar.ca.gov/fal/Member/Detail/204797",true);
		//$this->loadUrl("http://members.calbar.ca.gov/fal/Member/Detail/68409",true);
		
	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$np=new Name_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();		
		$x = new XPath($html);	
		$data = array();
		
			// get name
			foreach ($x->query("//h3[@class='srchresult']") as $node)
			{
				$data = array_merge($data, $np->parse(preg_replace("/\- #[0-9]+/","",$node->textContent)));
				break;
			}
			
			foreach ($x->query("//h3[contains(text(),'Current Status')]") as $node)
			{
				$data['STATUS'] = self::cleanup(str_replace("Current Status:","", $node->textContent));
				break;
			}
			
			
			foreach ($x->query("//table[@class='tblMemberDetail']//td//strong") as $node)
			{				
				$key = $value = "";
				// this is the key
				$key = self::cleanup(str_replace(":","",$node->textContent));
				
				
				// the value is the strong parents sibling td.
				$value = self::cleanup($node->parentNode->nextSibling->textContent);
								
				if ($key == "Sections" || $key == "District" )// these are wrapped in a href
				{
					$value  = self::cleanup($node->parentNode->parentNode->parentNode->textContent);
				}
					///			log::info("Key: $node->textContent");
				///log::info("Value: $value");
				
				if ($value == "")
				{
					$value = str_replace("$key:","", self::cleanup($node->parentNode->textContent));
				}
				if (!$value)
				{
					$value = preg_replace("/.*$key:/","", self::cleanup($node->parentNode->parentNode->textContent));
				}
				
				if ($key == 'Address' )				
				{			
					$data = array_merge($data, $ap->parse($value));	
							$key="";
				}
				else if ($key == 'Phone Number' || $key == 'Fax Number')
				{
					//$data = array_merge($data, $pp->parse(preg_replace("/\- #[0-9]+/","",$value)));
					
					if (preg_match("/($key: .\d{3}. [0-9]+\-[0-9]+)/",self::cleanup($node->parentNode->textContent),$matches))
					{
						$value = self::cleanup(str_replace("$key:","",$matches[1]));						
					}
				}
				
				else if ($key == 'County')
				{
					if (preg_match("#(.+)District:(.+)#",$value,$matches))
					{
						log::info($matches);
						$data['COUNTY']= self::cleanup($matches[1]);
						$data['District']= self::cleanup($matches[2]);
					}
						
				}				
				
				if(!isset($data[$key]) && $key)
					$data[$key] = $value;
				
			}
			
					
			// to get email we have to grab the correct css
			$emailId = false;
			foreach($x->query("//style") as $node)
			{
				// we are looking for this:
				#e10{display:inline;}
				
				if (preg_match("/#(e[0-9]+){display:inline;}/",$node->textContent,$matches))
				{
					$emailId = $matches[1];
					break;
				}
			}
			
			foreach($x->query("//span[@id='$emailId']") as $node)
			{
				$data['e-mail'] = $node->textContent;
			}
				
			
			
			//Do some funky clean7up
			if ( preg_match("/Sections: (.+) Law School:.+/",$data['Sections'],$matches))
			{
				$data['Sections'] = self::cleanup($matches[1]);
			}
			if ( preg_match("/District: (.+) Sections:.+/",$data['District'],$matches))
			{
				$data['District'] =self::cleanup( $matches[1]);
			}		
			
			if ( preg_match("/County: (.+) District:.+/",$data['County'],$matches))
			{
				$data['County'] = self::cleanup($matches[1]);
			}
			
			
			
			if ( preg_match("/Sections: (.+)/",$data['Sections'],$matches))
			{
				$data['Sections'] = self::cleanup($matches[1]);
			}
			
			
			
			// final transformations
			
			$data['District'] =  self::cleanup(str_replacE("District:","",$data['District']));
			$data['County'] =  self::cleanup(preg_replace("/District:.+/","",$data['County']));
			$data['County'] =  self::cleanup(preg_replace("/Undergraduate School:.+/","",$data['County']));
			
			
			unset($data['Effective Date']);
			unset($data['Status Change']);
			unset($data['Disciplinary and Related Actions']);
			unset($data['Administrative Actions']);
			
			
			//$data = db::normalize($data);
			$data["SOURCE_URL"] = $url;
			log::info($data);			
			
			db::store($type,$data,array('SOURCE_URL'),true);	

	
	}
}

$r= new calbar();
$r->parseCommandLine();

