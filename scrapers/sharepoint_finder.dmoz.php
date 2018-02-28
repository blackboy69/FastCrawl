<?
include_once "config.inc";

class sharepoint_finder_dmoz extends baseScrape
{
    public static $_this=null;
	public $timeout = 120;
	
   public function runLoader()
   {		
		$type = get_class();		
		//$this->threads= 1;
		$this->noProxy=false;
		//$this->switchProxy(false);
		
		$domains = db::oneCol("Select DOMAIN from sharepoint_finder");
		
		foreach($domains as $domain)
		{		
			$urls[] = "http://www.dmoz.org/search?q=".$domain;
		}
		$this->loadUrlsByArray($urls);	
		
		//$this->loadUrl("http://www.dmoz.org/search?q=travelguides.com",true);
		
		
	}
	
	static function parse($url,$html, $response_headers)
	{
		$x = new xpath($html);
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		
		foreach($x->query("//div[@class='site-url-and-ref']") as $node)
		{
			$x2= new Xpath($node);
			$siteUrl=$siteRef = "";
			foreach($x2->query("//div[@class='site-url']//a") as $node2)
			{
				$siteUrl=$node2->getAttribute("href");
			}
			
			foreach($x2->query("//div[@class='site-ref']") as $node2)
			{$siteRef=@Html2Text::convert($node2->c14n());}
			
			$data = array();
			$data['DOMAIN'] = preg_replace("#^www\.#","",parse_url($siteUrl, PHP_URL_HOST));
			$siteRef = preg_replace("#\[.+\]#","",$siteRef);
			$siteRef = str_replace("(","",$siteRef);
			$siteRef = str_replace(")","",$siteRef);
			$cats = explode("/",$siteRef);
			$categories = array();
			foreach($cats as $cat)
			{
				$categories[] = str_replacE("_"," ", urldecode($cat));
			}
			
			$data['CATEGORIES']= join(", ", $categories);
			
			if (preg_match("#{$data['DOMAIN']}#",$url))
			{						
				db::store("sharepoint_finder",$data, array("DOMAIN"));	
				//db::store($type,$data, array("DOMAIN"));	
				log::info($data);		
				return; // there will be only one.
			}
			log::info("skipping {$data['DOMAIN']}");
		}
		
		
		
		
	}
}

$r = new sharepoint_finder_dmoz();
$r->parseCommandLine();

