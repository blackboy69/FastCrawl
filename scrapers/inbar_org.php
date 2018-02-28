<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class inbar_org extends baseScrape
{
    public static $_this=null;
	
	
	# THIS SHOULD BE RUN MULTIPROCESS OR THE SEARCH ID GUIDS MAY TIME OUT
	# inbar_org all
	# inbar_org parse
	# inbar_org parse

   public function runLoader()
   {
	   for($i=65;$i<90;$i++) 
	   {
		   
		   for($j=65;$j<90;$j++) 
			{				
				$urls[] = "http://www.inbar.org/search/newsearch.asp?bst=".chr($i).chr($j);
				
				 // call parse data to make sure everything is downloaded before continueing
				 
				 //$this->parseData();
			}
	   }
	   log::info(sizeof($urls));
	   $this->loadUrlsByArray($urls);
	   $this->loadUrl("http://www.inbar.org/search/newsearch.asp?bst=AB");
	   //$this->loadUrlsByZip("http://www.cbcworldwide.com/professionals/find/page?page=2&locationName=%ZIP%");
	   //$this->loadUrlsByZip("http://www.cbcworldwide.com/professionals/find/page?page=3&locationName=%ZIP%");
	   db::store("inbar_org",array("SOURCE_URL"=>1),array('SOURCE_URL'),true);		
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
		$np = new Name_Parser();
		$kvp = new KeyValue_Parser();	
		$urls = array();
		log::info($url);
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 
		//$query['page'];
		$data = array();
		$x =  new  XPath($html);	
		//log::info($html);
		$links = array();
		if (preg_match("#newsearch.asp#",$url))
		{
			// grab listings..
			foreach ($x->query("//iframe") as $node)
			{
				$links[] = self::relative2absolute($url, $node->getAttribute("src"));
			}
			
			$thiz->loadUrlsByArray($links,false,1);			
			
		}
		else if (preg_match("#/searchserver/people.aspx#",$url))
		{
			
			// grab listings..
			foreach ($x->query("//a[contains(@id,'MiniProfileLink')]") as $node)
			{
				$link = self::relative2absolute($url, $node->getAttribute("href"));
				
				if (!preg_match("#\"#",$link))
					$links[] = $link;
			}
			log::info($links);
			$thiz->loadUrlsByArray($links);			
			
		}
		else if (preg_match("#inbar.org/members#",$url))
		{

			foreach($x->query("//td[@id='SpTitleBar']") as $node)
			{
				$data=array_merge($data, $np->parse($node->textContent));
			}
			
			foreach($x->query("//td[@id = 'tdEmployerName']") as $node)
			{
				$data=array_merge($data, $ap->parse($node->textContent));				
			}
			
			foreach($x->query("//td[@id = 'tdWorkPhone']") as $node)
			{
				$data=array_merge($data, $pp->parse($node->textContent));				
			}
			
			foreach($x->query("//td") as $node)
			{
				$data=array_merge($data, $ep->parse($node->textContent));				
				if (isset($data['EMAIL']) && $data['EMAIL'] == 'admin@inbar.org')
					unset($data['EMAIL']);
					
			}
			
			
			$data['SOURCE_URL'] = $url;
			log::info($data);
			db::store($type,$data,array('SOURCE_URL'),true);			
		}
	}
}
 
$r= new inbar_org();
$r->parseCommandLine();

