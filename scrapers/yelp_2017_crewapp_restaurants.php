<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class yelp_2017_crewapp_restaurants extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		$this->maxRetries = 2;
		$this->timeout = 15;
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->debug=false;
		
		
		$loadBigFile = false;
		if ($loadBigFile) 
		{
			$yelpFile = "inputdata/restaurantrecords.txt";
			$urls == array();
			$i=0;
			
			// this does the yelp scrape
			$block =1024*1024;//1MB or counld be any higher than HDD block_size*2
			if( $fh = fopen($yelpFile, "r") ){ 
						$left='';
				while (!feof($fh)) {// read the file
				   $temp = fread($fh, $block);  
				   $lines = explode("\n",$temp);
				   $lines[0]=$left.$lines[0];
				   if(!feof($fh) )$left = array_pop($lines);           
				   foreach($lines as $k => $line){
					   //do smth with $line				   
					   $rec = json_decode($line,true);				   
					   $urls[] = $rec['url'];
					   
					   if ($i++%1000==0)
					   {
						   $this->loadUrlsByArray($urls);
						   log::info("$i loaded");
						   $urls = array();
					   }
					}
				 }
			}
			fclose($fh);
		}
		// main (slow)
		//83.149.70.159:13012
		
		// 3 min (fast! )
		// 163.172.48.109:15001
		// 163.172.36.181:15001
		
		$this->proxies[]="163.172.36.181:15001";
		$this->proxies[]="163.172.48.109:15001";
		$this->proxies[]="183.149.70.159:13012";
		$this->proxies[]="63.141.241.98:16001";
		$this->proxies[]="163.172.36.211:16001";
		$this->proxies[]="108.59.14.203:13010";
		$k = array_rand($this->proxies);
		$this->proxy = $this->proxies[$k];
	
		//$this->proxy = "83.149.70.159:13012";
		$this->threads=1;
		//$this->noProxy=false;
		
		/*
		$this->threads=25;
		$this->noProxy=true;
		*/
		$this->useDbProxy=false;		
	}
	 function getNextProxy($url)
	{
		$k = array_rand($this->proxies);
		$this->proxy = $this->proxies[$k];
		log::info("Proxy switched $this->proxy");
		sleep(10);
		return;
	}
	
	
	static $hostCount=array();
	static $yelpCount = 0;
	static function loadCallBack($url,$html,$arg3)
	{
		if (empty($url)) //timeout?
			return;

		$thiz = self::getInstance();
		if (preg_match("#www.yelp.com/visit_captcha#",$url))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Captcha page.");
					
			$html=null;
		}
		
		if (strpos($html,"Sorry, you're not allowed to access this page."))
		{
			$host = parse_url($url,PHP_URL_HOST);
			log::info("Sorry, you're not allowed to access this page.");
					
			$html=null;
		}
		/*
		
		if (preg_match("/yelp.com/", $url))
		{
			
			$thiz->yelp++;
			
			log::error("Got yelp.... $url");
			if ($thiz->yelp > 50)
			{
				log::error("Exiting");
				exit;
			}
		}	*/
		
		

		if (strlen($html)<5000)
		{$html=null;}
		baseScrape::loadCallBack($url,$html,$arg3);
		//if (preg_match("#www.yelp.com#",$url))
			//sleep(1);
	}



	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	
		log::info($url);
			$ep = new Email_Parser();
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();

		if (preg_match("#yelp.com/search#",$url))
		{
			// get biz links
			$urls = array();
			foreach($x->query("//a[contains(@class,'biz-name')]") as $node)
			{
				$href = $node->getAttribute("href");
				if (!preg_match("#adredir#",$href))
				{
					$urls[] = self::relative2absolute($url,$href);
				}
			}

			// get next page links
			foreach($x->query("//a[contains(@class,'pagination-links')]") as $node)
			{
				$href = $node->getAttribute("href");
				if (!preg_match("#adredir#",$href))
				{
					$urls[] = self::relative2absolute($url,$href);
				}
			}

			if (!empty($urls))
				$thiz->loadUrlsByArray($urls);	
		}
		else if (preg_match("#yelp.com#",$url))
		{
			



			$data = array();

			foreach ($x->query("//h1[contains(@class,'biz-page-title')]") as $node)
			{
				$data['COMPANY'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='telephone']") as $node)
			{
				$data['PHONE'] = trim($node->textContent);
			}
			foreach ($x->query("//a[contains(@href,'biz_redir?url=')]") as $node)
			{
				$href = trim($node->getAttribute("href"));
				$data["WEBSITE"] = urldecode($thiz->urlVar($href,"url"));
			}
			foreach ($x->query("//span[@itemprop='streetAddress']") as $node)
			{
				$data['ADDRESS'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='addressLocality']") as $node)
			{
				$data['CITY'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='addressRegion']") as $node)
			{
				$data['STATE'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='postalCode']") as $node)
			{
				$data['ZIP'] = trim($node->textContent);
			}
			foreach ($x->query("//span[@itemprop='reviewCount']") as $node)
			{
				$data['NUM_REVIEWS'] = trim($node->textContent);
			}

			// pull category
			$categories=array();
			foreach ($x->query("//span[@class='category-str-list']//a") as $node)
			{
				$categories[] = self::cleanup($node->textContent);
			}
			$data['CATEGORIES'] = join(",", $categories);

			
			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;

				if(preg_match("/ |[a-z]/", @$data['ZIP']))
					$data['COUNTRY'] = 'Canada';
				else
					$data['COUNTRY'] = 'United States';

				if (empty($data['PHONE']))
					return;

				
				log::info("{$data['CITY']}, {$data['STATE']},  {$data['COMPANY']}");	
				if(strlen($data['SOURCE_URL']) < 255)
				{				
					log::info($data);
					$id = db::store($type,$data,array('SOURCE_URL'));	
				}
				
				if (!empty($data['WEBSITE']))
				{
					$thiz->loadUrlsByArray(array($data['WEBSITE']),false,1);
				}
			}		
		}
		else // check the id
		{
			$query = array();
			parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip
			$url = trim(preg_replace("#\?id=[0-9]+#","",$url),"/");
			
			$searchUrl = $url;
			$searchUrl = str_replace("http://","%",$searchUrl);
			$searchUrl = str_replace("https://","%",$searchUrl);
			$searchUrl = str_replace("https://www.","%",$searchUrl);
			
			$data = db::query("SELECT * FROM $type where WEBSITE like '$searchUrl%'");
			
			
			if (!empty($data))
			{				
				$data=array_merge($data,$pp->parse(strip_tags($html)));
				
				log::info("!!!SECONDARY PARSE!!!!");
				
				if ( empty($data['EMAIL'])  )
				{
					foreach($x->query("//a[contains(@href,'mailto')]") as $node)
					{
						$data['EMAIL'] = $data['email'] = str_replace("mailto:","",$node->getAttribute("href"));
						break;
					}
				}
				if ( empty($data['EMAIL'])  )
				{
					$data=array_merge($data,$ep->parse(strip_tags($html)));
				}

			
				// did we find email or phone numbers?
				if ( ! empty($data['EMAIL'])  )
				{				
					log::info($data);
					if(strlen($data['SOURCE_URL']) < 255)
					{
						db::store($type,$data,array('SOURCE_URL'),true);
					}
				}
				// otherwise spider to the contact us page when both aren't already set.
				 else
				{
					$x = new  XPath($html);	

					foreach($x->query("//a[contains( translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'contact')]") as $node)
					{
						$href = self::relative2absolute($url,$node->getAttribute("href"));
						
						log::info("Found Contact us page");
						log::info($href);
						$data['WEBSITE']= $href;
						if(strlen($data['SOURCE_URL']) < 255)
						{
							db::store($type,$data,array('SOURCE_URL'),true);
						}
						$thiz->loadUrl($href);
					}
				}
			}
			else
			log::info("Unknown url: $url");

		}
	}
}

$r= new yelp_2017_crewapp_restaurants();
$r->parseCommandLine();

