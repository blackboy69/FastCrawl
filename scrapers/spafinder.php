<?
include_once "config.inc";

class spafinder extends baseScrape
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
				$this->threads=2;

//		$this->loadUrl("http://listings.spafinder.com/search");
		$urls = array();
		$this->loadUrl("http://listings.spafinder.com/search");
		for ($i=1;$i<1400;$i++)
		{
				$urls[] = "http://listings.spafinder.com/search?page=$i";
		}
		$this->loadUrlsByArray($urls);
		$this->clean($reparse=true);
		//$this->loadUrl("byronwhitlock.com/fastcrawl/casper.php?type=render&p1=http://www.spafinder.com/Spa/43160-The-Breakers-Palm-Beach");

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
		if (preg_match("#spafinder.com/search#",$url))
		{
			$urls = array();
			// load listings
			foreach ($x->query("//a[@class='spa-name']") as $node)
			{
				$href = $node->getAttribute("href");
				$hrefEncoded = urlencode(self::relative2absolute($url,$href));
				$urls[]= "http://byronwhitlock.com/fastcrawl/casper.php?type=render&p1=$hrefEncoded";
			}
			
			// load next pages
			foreach ($x->query("//ul[@class='pagination']//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$data = array();
			foreach ($x->query("//*[contains(@class,'hero-title')]") as $node)
			{
				$data['COMPANY_NAME'] =$node->textContent;
				break;
			}
			if (empty($data['COMPANY_NAME']))
			{
				foreach ($x->query("//h1[@class='columns']") as $node)
				{
					$data['COMPANY_NAME'] =$node->textContent;
					break;
				}
			}
			foreach ($x->query("//div[@class='business-phone']//a") as $node)
			{
				$data['PHONE'] =$node->textContent;
			}

			foreach ($x->query("//div[@class='hero-visit-website']//a") as $node)
			{
				$data['WEBSITE'] =$node->textContent;
			}
			// email
			foreach ($x->query("//a[contains(@href,'mailto')]") as $node)
			{
				$data['EMAIL'] = str_replace("mailto:","", $node->getAttribute("href"));
			}


			foreach ($x->query("//address") as $node)
			{
				$data = array_merge($data,$ap->parse($node->textContent));
			}

			foreach ($x->query("//a[contains(@href,'#reviews')]//span") as $node)
			{
				$data['NUM_REVIEWS'] = preg_replace("/[^0-9]/","", $node->textContent);
			}

			foreach ($x->query("//div[@class='stars-gold value-title']") as $node)
			{
				$data['AVG_RATING'] = $node->getAttribute("title");
			}

			$data['POWERED_BY_MINDBODY'] = 'NO';
			foreach($x->query("//img[contains(@src,'powered_by_mindbody.png')]") as $node)
			{
				$data['POWERED_BY_MINDBODY'] = 'YES';
			}

			$data['SOURCE_URL'] = $query['p1'];
			log::info($data);		
			db::store($type,$data,array('SOURCE_URL'));	
	
		}

	

	}
}

$r= new spafinder();
$r->parseCommandLine();

