<?
include_once "config.inc";

class sage_com extends baseScrape
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
//				$this->threads=1;

		$this->clean(true);
		$url = "localhost:86/casper.php?type=render&p1=";
		$url.= urlencode("http://www.sage.com/us/accountant/sage-one-accountants#locator?source=sage1-overview");

		$this->loadUrl($url);

	}


	public static function parse($url,$html)
	{
		if (preg_match("#byronwhitlock.com/#", $url))
		{
			parse_str(parse_url($url,PHP_URL_QUERY),$bw); // address and zip
			log::info("Found Fastcrawl Proxy, updating $url => {$bw['p1']}");
			$url = $bw['p1'];
		}

		$type = get_class();		
		$thiz = self::getInstance();

		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$kvp = new KeyValue_Parser();	

		log::info($url);

		$xListings = new xPath($html);

		foreach ($xListings->query("//div[@class='partnerDetails']") as $nodeListing)
		{

			$x = new Xpath($nodeListing);
			$data = array();

			foreach ($x->query("//h2") as $node)
			{
				$data["COMPANY_NAME"] = $node->textContent;
				break;
			}

			if (preg_match("/Contact: \n(.+)/mi",$nodeListing->textContent,$matches))
				$data['CONTACT'] = trim($matches[1]);
			
			foreach ($x->query("//p[@class='partnerAddress']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->textContent));
			}

			$data = array_merge($data,$pp->parse($nodeListing->textContent));
			$data = array_merge($data,$ep->parse($nodeListing->textContent));

			foreach ($x->query("//p[@class='partnerAddress']/following-sibling::p") as $node)
			{
				$data = array_merge($data,$kvp->parse($node->textContent));
			}

			foreach ($x->query("//a[@title='Visit their website']") as $node)
			{
					$data['WEBSITE'] =$node->getAttribute("href");
			}
			

			$data['SOURCE_URL'] = $url;
			log::info($data);		
			$id = db::store($type,$data,array('COMPANY_NAME','ADDRESS','CITY','PHONE'));	
		}		
	}
}

$r= new sage_com();
$r->parseCommandLine();

