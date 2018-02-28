<?
include_once "config.inc";
//R::freeze();

class savills_studley_offices_com extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;		
		$this->threads=1;		
		
		$this->loadUrl("http://www.savills-studley.com/our-firm-locations.htm");
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
		if (preg_match("#http://www.savills-studley.com/our-firm-locations.htm#",$url))
		{
			
			$urls = array();
			foreach ($x->query("//a[contains(@href, '/locations/')]") as $node)
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
		
			// get name
			foreach ($x->query("//span[@id='ctl00_Modules_ctl00_ctl00_lblName']") as $node)
			{	
				$data['BRANCH_LOCATION_NAME']= $node->textContent;
				break;
			}
			

			// address
			foreach ($x->query("//span[@id='ctl00_Modules_ctl00_ctl00_lblAddress']") as $node)
			{	
				$data = array_merge($data, $ap->parse($node->textContent));
			}
			foreach ($x->query("//span[@id='ctl00_Modules_ctl00_ctl00_divPhone']") as $node)
			{	
				$data['PHONE'] =$node->textContent;
			}
			
			foreach ($x->query("//span[@id='ctl00_Modules_ctl00_ctl00_divFax']") as $node)
			{	
				$data['FAX'] =$node->textContent;
			}
								
			foreach(array('Public Relations Contact', 'Office Overview','Community Involvement','Featured Tenant Services') as $text)
			{
				foreach ($x->query("//h2[text()='$text']/following-sibling::*") as $node)
				{	
					
					if ($node->tagName != 'p')
						break;

					$data["$text"] .= $node->textContent . "\r\n\r\n";				
				}
			}
			
			$i=1;
			foreach ($x->query("//div[@class='teamMember']") as $node)
			{	
				$x2 = new Xpath($node);
				
				foreach($x2->query("//h3") as $node2)
				{
					$data['BRANCH_MANAGER_'.$i.'_NAME'] = $node2->textContent;
				}
				
				foreach($x2->query("//h3//a") as $node2)
				{
					$data['BRANCH_MANAGER_'.$i.'_URL'] =self::relative2absolute($url, $node->getAttribute("href"));
				}
				
				foreach($x2->query("//span[@id='ctl00_Modules_ctl00_ctl00_rptManagers_ctl00_lblTitle']") as $node2)
				{
					$data['BRANCH_MANAGER_'.$i.'_TITLE'] = $node2->textContent;
				}
				
				foreach($x2->query("//span[@id='ctl00_Modules_ctl00_ctl00_rptManagers_ctl00_lblPhone']") as $node2)
				{
					$data['BRANCH_MANAGER_'.$i.'_PHONE'] = $node2->textContent;
				}				
				$i++;
			}
			
			
			$data = db::normalize($data);			
				
			$data["SOURCE_URL"] = $url;
			log::info($data);			
			
			db::store($type,$data,array('SOURCE_URL'),true);	

		}
	}
}

$r= new savills_studley_offices_com();
$r->parseCommandLine();

