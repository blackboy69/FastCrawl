<?
include_once "config.inc";
//R::freeze();

class aranewmark_com extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;		
		$this->threads=1;		
		
		$this->loadUrl("http://www.aranewmark.com/office-locator-and-contacts.html");
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
		if (preg_match("#aranewmark.com/office-locator-and-contacts.html#",$url))
		{
			
			$urls = array();
			foreach ($x->query("//a[contains(@href, '/offices/')]") as $node)
			{				
				$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			
			// next page links			
			//foreach ($x->query("//a[contains(@href, 'peopleresults.aspx?')]") as $node)
			//{				
			//	$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
			//}
			if (sizeof($urls)>0)			
					$thiz->loadUrlsByArray($urls);
		}
		else
		{		
			//Grab address info
			$contactInfo = array();	
			// address
			foreach ($x->query("//div[contains(@class,'officeLocation')]//div[@class='holder']") as $node)
			{	
				$contactInfo = array_merge($data, $ap->parse($node->textContent));
			}
			
			//phone
			foreach ($x->query("//div[contains(@class,'officeLocation')]//p[@class='tel']") as $node)
			{	
				$contactInfo['OFFICE_PHONE'] = $node->textContent;
			}
			//fax
			foreach ($x->query("//div[contains(@class,'officeLocation')]//p[@class='fax']") as $node)
			{	
				$contactInfo['OFFICE_FAX'] = $node->textContent;
			}
			
			$xListing = $x;
			foreach($xListing->query("//div[@class='windowBlinds']") as $nodeListing)
			{
				$x = new xPath($nodeListing);			
				
				// get name
				foreach ($x->query("//div[@class='wbHeader']") as $node)
				{	
					list($name,$title, $junk) = explode("-",$node->textContent);
					
					$data = array_merge($data, $np->parse($name));
					break;
				}
				
				foreach ($x->query("//div[@class='wbHeader']//em") as $node)
				{	
					$data['TITLE']= $node->textContent;
					break;
				}
				
				foreach ($x->query("//div[@class='phoneNumber']") as $node)
				{
					$data['PHONE']= $node->textContent;
				}
				
				foreach ($x->query("//div[@class='wbContent']") as $node)
				{	
					$data = array_merge($data, $ep->parse($node->textContent));
					$data['PROFESSIONAL_PROFILE'] = self::cleanup( $node->textContent);
				}				
										
				$data = array_merge($data, $contactInfo);
				$data = db::normalize($data);			
					
				$data["SOURCE URL"] = $url;
				log::info($data);			
				
				db::store($type,$data,array('FIRST_NAME','LAST_NAME','PHONE','SOURCE_URL'),true);	
			}

		}
	}
}

$r= new aranewmark_com();
$r->parseCommandLine();

