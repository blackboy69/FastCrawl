<?
include_once "config.inc";

class topinteractiveagencies extends baseScrape
{
    public static $_this=null;
	
	
   public function loadUrl2($url)
   {
	  return parent::loadUrl("http://localhost:86/casper.php?p1=".urlencode($url)."&type=render");
   }
   
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

		
		//$this->threads=2;
		$this->maxRetries=3;
		
		//$this->clean();
		//db::query("UPDATE RAW_DATA set parsed =  0 WHERE type ='$type'");
		
		
		$this->loadUrl2("http://www.topinteractiveagencies.com/digital/agency/north-america/united-states/blue-fountain-media/");
		
		$this->loadUrl2("http://www.topinteractiveagencies.com/digital-directory/north-america/");
		$this->loadUrl2("http://www.topinteractiveagencies.com/digital-directory/latin-america/");
		$this->loadUrl2("http://www.topinteractiveagencies.com/digital-directory/europe/");
		$this->loadUrl2("http://www.topinteractiveagencies.com/digital-directory/asia/");
		$this->loadUrl2("http://www.topinteractiveagencies.com/digital-directory/africa/");
		$this->loadUrl2("http://www.topinteractiveagencies.com/digital-directory/oceania/");
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query);
		
		if (isset( $query['p1']))
		{
			$url = $query['p1'];
			parse_str(parse_url($url,PHP_URL_QUERY),$query);
		}
		log::info($url);

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
					$x=new Xpath($html);
		if (preg_match("#topinteractiveagencies.com/digital-directory#",$url))
		{
			$urls=ARRAY();			
			foreach ($x->query("//h2[@class='indextitle']//a") as $node)
			{
				 $thiz->loadUrl2(self::relative2absolute($url, $node->getAttribute("href")));
			}
			
			foreach ($x->query("//div[@class='pagination']//a") as $node)
			{
				 $thiz->loadUrl2(self::relative2absolute($url, $node->getAttribute("href")));
			}
			
				
		}
		else if (preg_match("#topinteractiveagencies.com#",$url))
		{

			$data=array();
			foreach ($x->query("//title") as $node)
			{
				list($data['COMPANY_NAME'],$junk) = explode("|", $node->textContent);
			}
			
			if (empty($data['COMPANY_NAME']))
			{
				foreach ($x->query("//span[@itemprop='name']") as $node)
				{
					$data['COMPANY_NAME']= self::cleanup($node->textContent);
				}
			}
			foreach ($x->query("//div[@class='blogcontent']//p") as $node)
			{
				
				$keyvalues[] = str_replacE('http://', '', str_replacE('https://', '', $node->textContent));
				
			}
			

			$kv =$kvp->parse($keyvalues);	
			
			foreach ($kv as $k=>$v)
			{
				 if (strlen($k) > 20)
					 continue;
				 
					$data[$k] =  $v;								 
			}
			$data = array_merge($data, $ap->parse(join("\n", $keyvalues)));				
			$data = array_merge($data, $pp->parse(join("\n", $keyvalues)));				
			$data = array_merge($data, $ep->parse(join("\n", $keyvalues)));				
			
			$data = db::normalize($data)				;

			if (isset($data['LOCATION']))
				$data= array_merge($ap->parse($data['LOCATION']), $data);
			
			$data['SOURCE_URL'] = $url;
			
			log::info($data);
			$id = db::store($type,$data,array('SOURCE_URL'));	
			
		//	if (!empty($data['Web']))
			//	$thiz->loadUrl($data['Web']."?id=$id");
		}
		else // check the id
		{
			/*
			if (!empty($query['id']))
			{
				$id = $query['id'];

				$data = db::query("SELECT * FROM $type where id = $id");
				$data=array_merge($data,$ep->parse(strip_tags($html)));
				$data=array_merge($data,$pp->parse(strip_tags($html)));
				
				log::info("!!!SECONDARY PARSE!!!!");

				// did we find email or phone numbers?
				if ( isset($data['EMAIL']) )
				{			
					log::info($data);
					db::store($type,$data,array('XID'),true);
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

					foreach($x->query("//a[contains( translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'about')]") as $node)
					{
						$href = self::relative2absolute($url,$node->getAttribute("href")) ;						
						log::info("Found about us page");
						log::info($href);
						$thiz->loadUrl($href."?id=$id");						
					}
				}
			}	
			*/
		}			
		
	}
}

$r= new topinteractiveagencies();
$r->parseCommandLine();

