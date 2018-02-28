<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class cresa_com extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$this->loadUrl("http://www.cresa.com/bios.aspx");
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
		//parse_str(parse_url($url,PHP_URL_QUERY),$query); 
		
		if (preg_match("#cresa.com/bios.aspx#",$url))
		{
			$xListing =  new  XPath($html);	
		
			foreach ($xListing->query("//div[@class='bioresultrow']") as $nodeListing)
			{
				$x = new Xpath($nodeListing);
				$links = $data = array();
				// grab listings..
				foreach ($x->query("//div[@class='bioresultname']//a") as $node)
				{
					$links[] = self::relative2absolute($url, $node->getAttribute("href"));
				}
				$thiz->loadUrlsByArray($links);
				
				foreach ($x->query("//div[@class='bioresultname']") as $node)
				{
					$data['NAME'] = self::cleanup($node->textContent);
				}
				
				foreach ($x->query("//div[@class='bioresulttitle']") as $node)
				{
					$data['TITLE'] = self::cleanup($node->textContent);
				}
				
				foreach ($x->query("//div[@class='bioresultlocation']") as $node)
				{
					$data['CITY'] = self::cleanup($node->textContent);
				}
				
				foreach ($x->query("//div[@class='bioresultphone']") as $node)
				{
					$data['PHONE'] = self::cleanup($node->textContent);
				}
					
				$data['SOURCE_URL'] = $url;				
				log::info($data);
				db::store($type,$data,array('NAME','CITY'),true);	
			}
		}
		else
		{
			$data = array();
			$x =  new  XPath($html);	
			foreach($x->query("//h1") as $node)
			{
				$data['NAME'] = self::cleanup($node->textContent);
			}
			
			foreach($x->query("//h2") as $node)
			{
				$data['TITLE'] =self::cleanup($node->textContent);
			}
			
			foreach($x->query("//h3[@class='showbioofficename']") as $node)
			{
				$data['OFFICE_NAME'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//div[@class = 'showbioofficedetails']") as $node)
			{
				$data=array_merge($data, $ap->parse($node->c14n()));
				$data=array_merge($data, $pp->parse($node->textContent));
				$data=array_merge($data, $ep->parse($node->textContent));
			}
			
			$tabs=array();
			$i=0;
			foreach($x->query("//div[@class = 'showbiotabs']//a") as $node)
			{
				$tabs[$i++]=self::cleanup($node->textContent);
			}
			
			$i=0;
			foreach($x->query("//div[contains(@class,'showbiotabbody')]") as $node)
			{
				$k = $tabs[$i++];	
				$data[$k] = @Html2Text::convert($node->c14n());
			}
			
			$data['SOURCE_URL'] = $url;
			log::info($data);
			db::store($type,$data,array('NAME','CITY'),true);			
		}
	}
}

$r= new cresa_com();
$r->parseCommandLine();

