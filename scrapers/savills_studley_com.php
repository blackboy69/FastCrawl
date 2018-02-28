<?
include_once "config.inc";
//R::freeze();

class savills_studley_com extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;		
		$this->threads=1;		
		
		$this->loadUrl("http://www.savills-studley.com/contact/people-results.aspx?name=&country=&office=&sector=");
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
		if (preg_match("#/contact/people-results.aspx#",$url))
		{
			
			$urls = array();
			foreach ($x->query("//a[contains(@href, '/bios/')]") as $node)
			{				
				$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			
			foreach ($x->query("//a[contains(@href, 'people-results.aspx?')]") as $node)
			{				
				$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			if (sizeof($urls)>0)			
					$thiz->loadUrlsByArray($urls);
		}
		else
		{		
		
			// get name
			foreach ($x->query("//h1") as $node)
			{	
				$data = array_merge($data, $np->parse($node->textContent));
				break;
			}
			
			// get title
			foreach ($x->query("//div[@class='studley_profile']//p") as $node)
			{	
				list($data['TITLE'],$data['OFFICE'],$data['PHONE']) = split("<br>", $node->c14n());
				
				$data['TITLE'] = strip_tags(self::cleanup($data['TITLE']));
				$data['PHONE'] = strip_tags(self::cleanup($data['PHONE']));
				$data['OFFICE'] = strip_tags(self::cleanup($data['OFFICE']));
				break;
			}
			
			
			
			// get profile
			foreach ($x->query("//div[@id='content_container']") as $node)
			{	
				$data['PROFESSIONAL_PROFILE'] = @Html2Text::convert($node->c14n());
				break;
			}
			
			// address
			foreach ($x->query("//div[@class='studley_profile']") as $node)
			{	
				$data = array_merge($data, $ap->parse($node->textContent));
				$data = array_merge($data, $ep->parse($node->textContent));
				$data = array_merge($data, $pp->parse($node->textContent));
			}
									
			$data = db::normalize($data);			
				
			$data["SOURCE URL"] = $url;
			log::info($data);			
			
			db::store($type,$data,array('FIRST_NAME','LAST_NAME','RAW_ADDRESS'),true);	

		}
	}
}

$r= new savills_studley_com();
$r->parseCommandLine();

