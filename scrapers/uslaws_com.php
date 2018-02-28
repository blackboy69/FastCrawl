<?
include_once "config.inc";
//R::freeze();

class uslawns_com extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;		
		$this->threads=1;		
		
		$this->loadUrl("http://uslawns.com/wp-admin/admin-ajax.php?action=get_locations");
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
		if (preg_match("#uslawns.com/wp-admin/admin-ajax.php#",$url))
		{
			
			$d = json_decode($html,true);
			
			$urls = array();
			foreach ($d as $listing)
			{				
				$urls[] = self::relative2absolute($url, $listing['link']);
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
			foreach ($x->query("//div[@class='personnel']//p[last()]") as $node)
			{	
				$contactInfo = $ap->parse($node->textContent);
			}
			
						
			$xListing = $x;
			foreach($xListing->query("//div[@class='personnel']//p") as $nodeListing)
			{
				$data1 = $contactInfo;
				
				$x = new xPath($nodeListing);							
				
				list($name, $title, $junk)  = explode('<br>', $nodeListing->c14n());
				 
				
				$data['TITLE']= self::cleanup(strip_tags($title));
				$data = array_merge($data, $np->parse(self::cleanup(strip_tags($name))));
				$data = array_merge($data, $pp->parse($nodeListing->c14n()));
				$data = array_merge($data, $ep->parse($nodeListing->c14n()));
				
				
				$data = array_merge($data, $contactInfo);
				$data = db::normalize($data);			
					
				$data["SOURCE URL"] = $url;
				log::info($data);			
				
				db::store($type,$data,array('FIRST_NAME','LAST_NAME','PHONE','SOURCE_URL'),true);	
			}

		}
	}
}

$r= new uslawns_com();
$r->parseCommandLine();

