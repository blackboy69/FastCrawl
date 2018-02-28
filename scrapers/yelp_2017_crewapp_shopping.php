<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";
/*
delete from 
load_queue
where url not in (select url from raw_data where type ='yelp_yelp_2017_crewapp_shopping' and url like '%yelp.com%' and strlen(html) < 3500)
and  type ='yelp_yelp_2017_crewapp_shopping' and url like '%yelp.com%'

*/
class yelp_2017_crewapp_shopping extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		$type = get_class();		
		$this->timeout = 45;
		//$this->maxRetries = 5;
/*
		//$this->maxRetries = 25;
		
		$this->useCookies=false;
		$this->allowRedirects = true;*/
		$this->debug=false;
		
		
		
		//db::query("update load_queue set processing=1 where processing=0 and type ='$type'");
		$loadBigFile = false;
		if ($loadBigFile) 
		{
			$yelpFile = "inputdata/shoppingRecords.txt";
			$urls = array();
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
					   $url =$rec['url'];
					   $pos=strpos($url,"?");
					   $url = substr($url,0,$pos);
					   $urls[] =$url;// $rec['url'];// substr($url,0,$pos);
					   
					   if ($i++%1000==0)
					   {
						   if ($i > 323000)
						   {
							$this->loadUrlsByArray($urls);
							log::info("$i loaded");
							log::info($url);
						   }
						   else
							   log::info("$i skipped");
						   $urls = array();
					   }
					}
				 }
			}
			fclose($fh);
		}
		// main (slow)
		//Use this for storm proxies!
		// go to stormproxies and update the proxies copy paste to here
$this->proxies[]="108.59.14.208:13041";
$this->proxies[]="37.48.118.90:13041";
$this->proxies[]="83.149.70.159:13041";
$this->proxies[]="108.59.14.203:13041";

$this->proxies[]="37.48.118.90:13042";
$this->proxies[]="83.149.70.159:13042";
$this->proxies[]="83.149.70.159:13012";
$this->proxies[]="163.172.48.109:15002";
$this->proxies[]="163.172.48.117:15002";
$this->proxies[]="163.172.48.119:15002";
$this->proxies[]="163.172.48.121:15002";
$this->proxies[]="163.172.36.181:15002";
$this->proxies[]="163.172.36.191:15002";
$this->proxies[]="163.172.36.197:15002";
$this->proxies[]="163.172.36.207:15002";
$this->proxies[]="163.172.48.109:15001";
$this->proxies[]="163.172.36.181:15001";
$this->proxies[]="63.141.241.98:16001";
$this->proxies[]="199.168.137.38:16001";
$this->proxies[]="163.172.36.211:16001";
$this->proxies[]="163.172.36.213:16001";
$this->proxies[]="63.141.241.98:16001";
$this->proxies[]="163.172.36.211:16001";


		// don't change settings dude it will fuck shit up		
		
		
		
		// so your going to do it anyway? you have been warned.
		// so multiple processes don't start at the same point in the proxy pool
		$this->proxyIdx=rand(0,sizeof($this->proxies));
		
		$this->getNextProxy(null);
		// deont change #threads to more than 8
		$this->threads=12;		
		$this->noProxy=false;		
		$this->useDbProxy=false;	
		
		

		
	}
	function getNextProxy($url=null)
	{		
		$k= $this->proxyIdx++ % sizeof($this->proxies);
		
		$this->proxy = $this->proxies[$k];
		log::info("Proxy switched $k, $this->proxy");
		$this->proxyMap[$url] = $url;
	
		sleep(1);
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
		
		
		if (preg_match("/yelp.com/", $url))
		{
			
			if (strlen($html)<3500)
			{$html=null;}
		}	
		
		

		//if (strlen($html)<5000)
		//{$html=null;}
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

$r= new yelp_2017_crewapp_shopping();
$r->parseCommandLine();

