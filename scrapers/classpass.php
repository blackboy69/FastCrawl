<?
include_once "config.inc";

class classpass extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
/*		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=8;
		$this->debug=false;
	*/	
				$this->threads=10;

//		$this->loadUrl("http://listings.classpass.com/search");
		$urls = array();
		$this->loadUrl("https://classpass.com/vertical-method-san-francisco");
		for ($i=1;$i<30;$i++)
		{
				$urls[] = "https://classpass.com/a/GetStudios?msaId=$i";
		}
		$this->clean($noDelete=TRUE);
		$this->loadUrlsByArray($urls);
		

		//$this->loadUrl("byronwhitlock.com/fastcrawl/casper.php?type=render&p1=http://www.classpass.com/Spa/43160-The-Breakers-Palm-Beach");

	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		if (preg_match("#classpass.com/a/GetStudios#",$url))
		{
			$json = json_decode ($html,true);
			$urls=ARRAY();
			foreach ($json['response']['studios'] as $studio)
			{
				$urls[] = "https://classpass.com/".$studio['alias'];
			}
			$thiz->loadUrlsByArray($urls);
		}
		else if (preg_match("#classpass.com#",$url))
		{
			$data = array();
			foreach ($x->query("//h6[text()='Studio']/following-sibling::h1") as $node)
			{
				$data['COMPANY_NAME'] =$node->textContent;
				break;
			}
			
			foreach ($x->query("//address") as $node)
			{
				$data = array_merge($data,$ap->parse($node->textContent));
			}

			foreach ($x->query("//p[@class='tel']") as $node)
			{
				$data['PHONE'] =$node->textContent;
			}

			foreach ($x->query("//div[@class='information']//a") as $node)
			{
					$data['WEBSITE'] =$node->textContent;
			}
			
			//secondary scrape for website.
		
			$data['POWERED_BY_MINDBODY'] = 'NO';
			foreach($x->query("//img[contains(@src,'mindbody-powered-logo.png')]") as $node)
			{
				$data['POWERED_BY_MINDBODY'] = 'YES';
			}


			$data['SOURCE_URL'] = $url;
			log::info($data);		
			$id = db::store($type,$data,array('SOURCE_URL'));	
	
			if (!empty($data['WEBSITE']))
			{
///				$thiz->loadUrl("byronwhitlock.com/fastcrawl/casper.php?type=render&p1=http://".urlencode($data['WEBSITE'])."&id=$id");
				$thiz->loadUrl("http://".$data['WEBSITE']."?id=$id");
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

$r= new classpass();
$r->parseCommandLine();

