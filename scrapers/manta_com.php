<?
include_once "config.inc";

class manta extends baseScrape
{
    public static $_this=null;
	
	var $CATEGORY;

   public function runLoader()
   {
		$type = get_class();	
		//$this->proxyUserPass = "9d5156a4c47d40c68da101d352b96f55:";
		$this->noProxy=false;
		$this->useDbProxy=true;
		$this->reloadPublicProxyList();
		$this->workChunkSize=100;
		
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=8;
		$this->debug=false;
	
		$this->timeout=30;
		$this->threads=1;

		
		//$this->clean();
		$urls = array();
		$this->switchProxy(null,true);
//		$this->clean($reparseOnly=false);
		//db::query("DELETE FROM load_queue where type='$type'");	
		//db::query("DELETE FROM raw_data where type='$type'");
		///$this->loadUrl("http://www.manta.com/Spa/43160-The-Breakers-Palm-Beach?Sd",true);
		db::query("update raw_data set parsed =0 where parsed =1 and type = 'manta' and url ='http://www.manta.com/c/mm8ylzn/cooper-electric'");
		db::query("update raw_data set parsed =0 where parsed =1 and type = 'manta' and url ='http://www.manta.com/c/mm8b07g/yountville-plumbing'");
		db::query("update raw_data set parsed =0 where parsed =1 and type = 'manta' and url ='http://www.manta.com/c/mms0n1y/rock-sons-inc'");
		db::query("update raw_data set parsed =0 where parsed =1 and type = 'manta' and url ='http://www.manta.com/c/mtmkk8k/coastline-electric-company'");
		
		//$this->clean(true);
	//	$this->CATEGORY = "electrical_work";
		//$this->loadUrl("http://www.manta.com/mb_45_E12DB000_05/$this->CATEGORY/california?show_all_cities=1");
		//$this->loadUrl("byronwhitlock.com/fastcrawl/casper.php?type=render&p1=$url");
		//$this->loadUrl("http://www.manta.com/c/mtmkk8k/coastline-electric-company",true);
		//$this->loadUrl("http://google.com");
		
		$this->loadUrl("http://www.manta.com/mb_45_E12DB000_05/electrical_work/california?show_all_cities=1");
		$this->loadUrl("http://www.manta.com/mb_45_B82C705K_05/plumbing_contractors/california?show_all_cities=1");
		
	}

	static function loadCallBack($url,$html,$arg3)
   {
      // we move very slow
		//sleep(10);
			if (strlen ($html) < 2000)
				$html = '';
      baseScrape::loadCallBack($url,$html,$arg3);
   }

	public static function parse($url,$html)
	{
		///file_put_Contents("manta.html",$html);

		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		//if (preg_match("#/$thiz->CATEGORY/#",$url))
		{
			//log::info("Not Parsing listing $url");
			$urls = array();
			// load cities
			foreach ($x->query("//span[contains(text(),'Browse cities')]/following-sibling::div//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			
			// load next pages
			foreach ($x->query("//ul[@class='pagination']//a") as $node)
			{
				$urls[] =  self::relative2absolute($url,$node->getAttribute("href"));
			}
			
			// load listings
			foreach ($x->query("//a[@itemprop='name']") as $node)
			{
				$urls[] =  self::relative2absolute($url,$node->getAttribute("href"));
			}
		
			if (sizeof($urls))
				$thiz->loadUrlsByArray($urls);
		}
		//else // parse the listing
		{

			// two types of listings
			// feature listings and regular ones.
			$listingType=0;
			
			$data = array();
			foreach ($x->query("//h1[@itemprop='name']") as $node)
			{
				$data['COMPANY_NAME'] =self::cleanup($node->textContent);
				break;
			}
			//foreach ($x->query("//body") as $node)
			//{
//				log::info($node->textContent);
	//		}
			if (!empty($data['COMPANY_NAME']))
			{
				// contact info
				foreach ($x->query("//div[contains(text(),'Contact Information')]/following-sibling::div") as $node)
				{
					$data = array_merge($data,$kvp->parse($node->textContent));
				/* $data = array_merge($data,$ap->parse($node->textContent));
					$data = array_merge($data,$ep->parse($node->textContent));
					$data = array_merge($data,$pp->parse($node->textContent));*/
				}

				
				foreach ($x->query("//div[@itemprop='address']") as $node)
				{
					$data = array_merge($data,$ap->parse($node->textContent));

					
				/* $data = array_merge($data,$kvp->parse($node->textContent));
					$data = array_merge($data,$ep->parse($node->textContent));
					$data = array_merge($data,$pp->parse($node->textContent));*/
				}

				foreach ($x->query("//div[@itemprop='telephone']") as $node)
				{
					$rel = $node->getAttribute('rel');
					$data['PHONE'][$rel] = trim(str_ireplace("Phone:","", $node->textContent));
				}	
				
				foreach ($x->query("//div[@itemprop='email']") as $node)
				{
					$data['EMAIL'] = trim(str_ireplace("Email:","", $node->textContent));
				}
								
				foreach ($x->query("//a[contains(text(),'Web: ')]") as $node)
				{
					$data['WEBSITE'] = urldecode(str_ireplace("/api/v1/urlverify/","", $node->getAttribute("href")));
				}				

				foreach($x->query("//ol[@class='breadcrumb']//a[last()]") as $node)
				{
					$data['CATEGORY'] = self::cleanup($node->textContent);
				}

				$products = array();
				foreach($x->query("//h3[text()='Products & Services']/following-sibling::div//li") as $node)
				{
					$products[] = self::cleanup($node->textContent);
				}
				$data['PRODUCTS_SERVICES'] = join(", ",$products);
				
				foreach($x->query("//td[@itemprop='foundingDate']") as $node)
				{
					$data['YEAR ESTABLISHED'] = self::cleanup($node->textContent);
				}

				foreach($x->query("//td[@rel='numEmployees']") as $node)
				{
					$data['EMPLOYEES'] = self::cleanup($node->textContent);
				}

				$i=0;
				foreach ($x->query("//li[@itemprop='employee']") as $node)
				{
					$i++;
					$x2= new Xpath($node);
					$contact = array();
					foreach($x2->query("//span[@itemprop='name']") as $node2)
					{
						$data['CONTACT_'.$i . '_NAME'] =  trim($node2->textContent);
					}
					
					foreach($x2->query("//span[@itemprop='jobTitle']") as $node2)
					{
						$data['CONTACT_'.$i . '_TITLE'] =  trim($node2->textContent);
					}
					
					foreach($x2->query("//span[@itemprop='telephone']") as $node2)
					{
						$data['CONTACT_'.$i . '_PHONE'] =  trim($node2->textContent);
					}
					
					foreach($x2->query("//span[@itemprop='email']") as $node2)
					{
						$data['CONTACT_'.$i . '_EMAIL'] =  trim($node2->textContent);
					}
				}
				
				

				$data['SOURCE_URL'] = $url;
				
				log::info(db::normalize($data));		
				db::store($type,$data,array('SOURCE_URL'));	
			}
		}
	}
}

$r= new manta();
$r->parseCommandLine();

