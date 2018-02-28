<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class rainbowintl extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

//		$this->maxRetries = 100;
	//	$this->timeout = 15;
		//$this->useCookies=false;
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=8;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='myiclubonline' and LENGTH(html) < 3000)
			 AND type ='myiclubonline'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='myiclubonline'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='myiclubonline')
				  AND processing = 0
			     AND type ='myiclubonline'
		    )
		
			 ");
		
		*/
		// cananda top 100 cities by population
		//db::query("UPDATE raw_data set parsed = 0 where type='myiclubonline' and parsed = 1  ");
		//db::query("drop table $type");
		
	$this->maxRetries=1;
	//	$this->noProxy=true;
//		$this->switchProxy(null,true);

		$this->loadUrl("http://rainbowintl.com/locations-sitemap");
	}


	public static function parse($url,$html)
	{
		$type = get_class();		

		$x = new xpath($html);
		$thiz=self::getInstance();
		$type = get_class();	
		//parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$ap = new Address_Parser();
		$pp = new phone_parser();
		//$ep = new Email_Parser();
		//$np = new Name_Parser();
		
		if (preg_match("#rainbowintl.com/locations-sitemap#",$url))
		{
			
			foreach($x->query("//div[@class='view-content']//a") as $node)
			{
				$urls[] = self::relative2Absolute($url,$node->getAttribute("href"));
			}
			if (!empty($urls))
			{
				$thiz->loadUrlsByArray($urls);
			}
		}
		else
		{
			
			$data = array();
			foreach($x->query('//span[@itemprop="name"]') as $node)
			{
				$data['NAME'] = $node->textContent;
				break;
			}
					
			foreach($x->query("//span[@itemprop='address']") as $node)
			{
				$data = array_merge($data,$ap->parse(str_replacE("|"," ", $node->textContent)));
			}
			
			foreach($x->query("//span[@itemprop='telephone']") as $node)
			{
				$data = array_merge($data,$pp->parse($node->textContent));
			}
							

			$data['SOURCE_URL'] = $url;
			log::info($data);					
			db::store($type,$data,array('SOURCE_URL'));	
		}

	}
	
}

$r= new rainbowintl();
$r->parseCommandLine();

