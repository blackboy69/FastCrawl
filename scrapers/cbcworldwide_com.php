<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class cbcworldwide_com extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
	   for($i=1;$i<200;$i++) 
	   {
		$this->loadUrl("http://www.cbcworldwide.com/professionals/find/page?page=$i");
	   }
	   
	   $this->loadUrlsByZip("http://www.cbcworldwide.com/professionals/find/page?page=1&locationName=%ZIP%");
	   $this->loadUrlsByZip("http://www.cbcworldwide.com/professionals/find/page?page=2&locationName=%ZIP%");
	   $this->loadUrlsByZip("http://www.cbcworldwide.com/professionals/find/page?page=3&locationName=%ZIP%");
	}
	
/*
	static $hostCount=array();
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		if (strpos($html,"The Three Laws of Robotics are as follows:"))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("ERROR! ERROR! FORBIDDEN ACCESS!");
					
			$html=null;
		}

		if (strlen($html)<5000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
	}*/



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();

		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$kvp = new KeyValue_Parser();	
		$urls = array();
		log::info($url);
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 
		//$query['page'];
		$data = array();
		$x =  new  XPath($html);	
		
		if (preg_match("#cbcworldwide.com/professionals/find#",$url))
		{
			$links = array();
			if (sizeof($links) > 1 )
			{
				$page = $query['page']+1;
				$links[] =  "http://www.cbcworldwide.com/professionals/find/page?page={$page}";
			}	
			
			// grab listings..
			foreach ($x->query("//div[contains(@class,'results-card')]") as $node)
			{
				$links[] = self::relative2absolute($url, $node->getAttribute("data-profile-url"));
			}
			
			

						
			$thiz->loadUrlsByArray($links);
		}
		else
		{

			foreach($x->query("//div[@class='primary-contact']//h3//a") as $node)
			{
				$data['NAME'] = self::cleanup($node->textContent);
			}
			

			foreach($x->query("//div[@class = 'contact-info']") as $node)
			{
				$data=array_merge($data, $pp->parse($node->textContent));
				$data=array_merge($data, $ep->parse($node->textContent));
			}
			
			foreach ($x->query("//div[@id='specialties']") as $node)
			{
				$data['SPECIALTIES'] = @Html2Text::convert($node->c14n());
			}			
			foreach ($x->query("//div[@id='awards']") as $node)
			{
				$data['AWARDS'] = @Html2Text::convert($node->c14n());
			}						
			foreach ($x->query("//div[@id='languages']") as $node)
			{
				$data['LANGUAGES'] = @Html2Text::convert($node->c14n());
			}				
			
			foreach ($x->query("//div[@class='office-name']") as $node)
			{
				$data['OFFICE_NAME'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//div[@class='office-address']") as $node)
			{
				$data=array_merge($data, $ap->parse($node->textContent));
			}			
			
			foreach($x->query("//article") as $node)
			{
				$data["BIO"] = @Html2Text::convert($node->c14n());
			}
			
			$data['SOURCE_URL'] = $url;
			log::info($data);
			db::store($type,$data,array('NAME','CITY','EMAIL','PHONE'),true);			
		}
	}
}

$r= new cbcworldwide_com();
$r->parseCommandLine();

