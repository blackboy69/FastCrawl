<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class svn_com extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
	   
		$url="http://localhost:86/casper.php?type=render&p1=http%3A%2F%2Fproperties.svn.com%2Fplugins%2F4645366537fa970045b1ade520b9502e1419fc3d%2Fwww.svn.com%2Fbrokers%2F%3Foffset%3D600%26searchText%3D%CITY%";		   
		
		$this->loadUrlsByCity($url,'', 250);
	   
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
		$np = new Name_Parser();
		$isSearch=false;
		$urls = array();
		log::info($url);
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 
		if (isset($query['p1']))			
		{
			$url = $query['p1'];
			parse_str(parse_url($url,PHP_URL_QUERY),$query); 	
			$isSearch=true;
		}
		log::info($url);
		
	//	if (preg_match("#plugins/4645366537fa970045b1ade520b9502e1419fc3d/www.svn.com/brokers#",$url))
		//if($isSearch)
		
		{		
			$xListing =  new  XPath($html);	
							
			foreach ($xListing->query("//tr[@class='broker']") as $nodeListing)
			{
				$x = new Xpath($nodeListing);
				$data = array();
				
				// grab listings..
				foreach ($x->query("//div[@class='name']//a") as $node)
				{
					parse_str(parse_url($node->getAttribute("href"),PHP_URL_QUERY),$q); 
					$data['BROKER_ID'] = $q['brokerId'];
					$toLoad = "http://properties.svn.com/plugins/4645366537fa970045b1ade520b9502e1419fc3d/www.svn.com/brokers/{$data['BROKER_ID']}?pluginId=0&brokerId={$data['BROKER_ID']}";
					//$toLoad= "http://properties.svn.com/plugins/4645366537fa970045b1ade520b9502e1419fc3d/www.svn.com/brokers/{$data['BROKER_ID']}?tab=profile&brokerId={$data['BROKER_ID']}";
					log::info("Going ot load $toLoad");
					
					$thiz->loadUrl("http://localhost:86/casper.php?type=render&p1=".urlencode($toLoad));
					//$thiz->loadUrl($toLoad2);
					
				}
				
				foreach($x->query("//div[@class = 'address']") as $node)
				{
					$data=array_merge($data, $ap->parse(@Html2Text::convert($node->c14n())));
				}
				$data['SOURCE_URL'] = $url;				
					
				if (isset($data['BROKER_ID']))
				{
					log::info($data);
					db::store($type,$data,array('BROKER_ID'),true);	
				}
			}
		}
		if (! isset($data['BROKER_ID']))
		{
			$data = array();
			$x =  new  XPath($html);	
			foreach($x->query("//div[@class='name']") as $node)
			{
				$data['NAME'] = self::cleanup($node->textContent);
			}
			
			foreach($x->query("//div[@class='corporateJobTitle']") as $node)
			{
				$data['TITLE'] =self::cleanup($node->textContent);
			}
			
			foreach($x->query("//div[@class='corporateCompanyName']") as $node)
			{
				$data['COMPANY_NAME'] =self::cleanup($node->textContent);
			}			
			
			foreach($x->query("//div[@class = 'licenseInfo']") as $node)
			{
				$data['LICENSE'] =self::cleanup($node->textContent);
			}
			foreach($x->query("//div[@class = 'contactInfo']") as $node)
			{
				$data=array_merge($data, $pp->parse(@Html2Text::convert($node->c14n())));
				$data=array_merge($data, $ep->parse(@Html2Text::convert($node->c14n())));
			}
			
			foreach($x->query("//div[@class='biography']") as $node)
			{
				$data['BIOGRAPHY'] = @Html2Text::convert($node->c14n());
				break;
			}
			
			foreach($x->query("//div[@class='specialties']") as $node)
			{
				$data['SPECIALTIES'] = @Html2Text::convert($node->c14n());
				break;
			}
			
			log::info($query);
				log::info($data);
			$data['BROKER_ID']=$query['brokerId'];			
			$data['SOURCE_URL'] = $url;
			
			
				
			if (isset($data['BROKER_ID']))
			{
				log::info($data);
				db::store($type,$data,array('BROKER_ID'),true);	
				
			}			
		}
	}
}

$r= new svn_com();
$r->parseCommandLine();

