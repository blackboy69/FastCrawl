<?
include_once "config.inc";

class bicycleshops_us extends baseScrape
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
		
		$this->clean(false);

		$this->threads=10;		
		$this->loadUrl("http://www.bicycleshops.us/");
		
		
	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$xListing = new XPath($html);	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		
		if (preg_match("#bicycleshops.us#",$url))
		{

			$urls=ARRAY();			
			foreach ($xListing->query("//td[@class='g']//a") as $nodeListing)
			{
				 $urls[] = $nodeListing->getAttribute("href");
			}
			
							
			print_r($urls);
			
			exit;
			
			if (sizeof($urls)>0)
			{
				$thiz->loadUrlsByArray($urls);
				return;
			}

			
			foreach ($xListing->query("//table[@class='b']//tr") as $nodeListing)
			{							
			
				$x= new Xpath($nodeListing);
				$data = array();
				foreach ($x->query("//td[@width='300']//a") as $node)
				{
					$data['COMPANY_NAME'] =$node->textContent;
					$data['WEBSITE'] =$node->getAttribute("href");
				}			
							
				foreach ($x->query("//td[@width='300']") as $node)
				{
					$data = array_merge($data, $pp->parse($node->textContent));
					$data = array_merge($data, $ap->parse($node->textContent));					
				}
				
				$cat = array();
				foreach ($x->query("//td[@width='280']//li") as $node)
				{
					$cat[] = $node->textContent;					
				}
				$data['CATEGORY'] = join(", ", $cat);
				
				$data['SOURCE_URL'] = $url;

				$id = db::store($type,$data,array('COMPANY_NAME','CITY','PHONE'));	
		print_r($data);
				if (!empty($data['WEBSITE']))
					$thiz->loadUrl($data['WEBSITE']."?id=$id");
			}
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
					db::store($type,$data,array('COMPANY_NAME','CITY','PHONE'),true);
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

$r= new bicycleshops_us();
$r->parseCommandLine();

