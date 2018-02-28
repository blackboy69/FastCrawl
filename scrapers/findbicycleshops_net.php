<?
include_once "config.inc";

class findbicycleshops_net extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		/*	
			
			$this->proxy = "localhost:8888";

			$this->useCookies=false;
			$this->allowRedirects = true;
			
			$this->debug=false;
		*/	

		
		//$this->threads=2;
		$this->maxRetries=3;
		$this->loadUrl("http://findbicycleshops.net/");
		//$this->clean(true);
		$this->loadUrl("http://findbicycleshops.net/shops/california/hub_cyclery.html");
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
		
		if (preg_match("#findbicycleshops.net#",$url))
		{
			$x= new Xpath($html);
			$urls=ARRAY();			
			foreach ($x->query("//table[@class='Dir']//a") as $nodeListing)
			{
				 $urls[] = self::relative2absolute($url, $nodeListing->getAttribute("href"));
			}
			
			foreach ($x->query("//table[@id='Entries']//a") as $nodeListing)
			{
				 $urls[] = self::relative2absolute($url, $nodeListing->getAttribute("href"));
			}
			
			$thiz->loadUrlsByArray($urls);
			if (sizeof($urls)>0)
				return;
			
			
							
			foreach ($x->query("//h1") as $node)
			{
				$data['COMPANY_NAME'] =$node->textContent;
				
			}			
				
			foreach ($x->query("//span[@style='font-size: .9em;']") as $node)
			{
				$data = array_merge($data, $pp->parse($node->textContent));
				$data = array_merge($data, $ap->parse($node->textContent));					
			}
			
			foreach ($x->query("//span[@style='font-size: .9em;']//a[last()]") as $node)
			{
				$data['WEBSITE'] =$node->getAttribute("href");		
			}
			
			$data['SOURCE_URL'] = $url;

			$id = db::store($type,$data,array('COMPANY_NAME', 'ADDRESS','CITY','SOURCE_URL'));	
			log::info($data);
			if (!empty($data['WEBSITE']))
				$thiz->loadUrl($data['WEBSITE']."?id=$id");
		
		}
		else // check the id
		{
			if (!empty($query['id']))
			{
				$id = $query['id'];

				$data = db::query("SELECT * FROM $type where id = $id");
				$data=array_merge($data,$ep->parse(strip_tags($html)));
				//$data=array_merge($data,$pp->parse(strip_tags($html)));
				
				log::info("!!!SECONDARY PARSE!!!!");

				// did we find email or phone numbers?
				if ( isset($data['EMAIL']) )
				{			
					log::info($data);
					db::store($type,$data,array('COMPANY_NAME', 'ADDRESS','CITY','SOURCE_URL'),true);
				}
				// otherwise spider to the contact us page when both aren't already set.
				else 
				{
					$x = new  XPath($html);	

					foreach($x->query("//a[contains( translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'contact')]") as $node)
					{


						$href = self::relative2absolute($url,$node->getAttribute("href")) ;						
						log::info("Found Contact us page");
						log::info($href);
						$thiz->loadUrl($href."?id=$id");						
					}
				}
			}	
		}			
		
	}
}

$r= new findbicycleshops_net();
$r->parseCommandLine();

