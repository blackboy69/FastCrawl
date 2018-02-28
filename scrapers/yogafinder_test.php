<?
include_once "config.inc";

class yogafinder_test extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
/*		$this->noProxy=true;
		//$this->proxy = "localhost:8888";
12
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=8;
		$this->debug=false;
	*/	
				$this->threads=2;

//		$this->loadUrl("http://listings.yogafinder.com/search");
		$urls = array();
		
//		$this->clean();

	//	$this->loadUrl("http://www.yogafinder.com/yogaarea.cfm?yogacountry=USA&yogamap=Hide");
		//$this->loadUrl("http://www.yogafinder.com/yogaarea.cfm?yogacountry=Canada&yogamap=Hide");

		$this->loadUrl("www.yogafinder.com/yogatracking.cfm?yoganumber=43118");

	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		if (preg_match("#yogafinder.com/yogaarea.cfm#",$url))
		{
			$urls = array();
			// load city links
			foreach ($x->query("//a[contains(@href,'yogacity.cfm')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);
		}
		else if (preg_match("#yogafinder.com/yogacity.cfm#",$url))
		{
			$urls = array();
			// load city links
			foreach ($x->query("//a[contains(@href,'yoga.cfm')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);
		} 

		else//*[@id="bluelinearound"]/table/tbody/tr[1]/td[1]/div/font/text()
		{
		
			$xTop = new XPath($html);

			foreach ($xTop->query("//div[@id='bluelinearound']") as $nodeTop)
			{
				$x = new Xpath($nodeTop);
				$data = array();

				foreach ($x->query("//div[@class='insidetext'][1]") as $node)
				{
					$data['COMPANY_NAME'] = self::cleanup($node->textContent);
					break;
				}
				
				if (empty($data['COMPANY_NAME']))
					continue;

				foreach ($x->query("(//td[1])[2]") as $node)
				{
					$data = array_merge($data,$ap->parse($node->textContent));
					break;
				}
				
				$data['CONTACT'] ="";
				foreach ($x->query("//*[contains(text(),'Contact:')]") as $node)
				{
					$data['CONTACT'] = self::cleanup( str_ireplace("Contact:","", $node->textContent) );
					break;
				}


				foreach ($x->query("//tr[2]/td[2]") as $node)
				{
					$data['PHONE'] =  self::cleanup($node->textContent);
					$data =  array_merge($data,$pp->parse($node->textContent)); // format if possible
					break;
				}

				foreach ($x->query("//td[2]") as $node)
				{
					$data['SPECIALTY'] = self::cleanup($node->textContent);
					break;

				}

				foreach ($x->query("//a[contains(@href,'yogatracking.cfm')]") as $node)
				{
					$href = self::relative2absolute($url,$node->getAttribute("href"));
					$data['WEBSITE'] = self::getFinalAddress($href);
				}

				$data['SOURCE_URL'] = $url ;
				log::info($data);		
				db::store($type,$data,array('COMPANY_NAME','PHONE','ADDRESS'));	
			}
	
		}

	

	}
}

$r= new yogafinder_test();
$r->parseCommandLine();

