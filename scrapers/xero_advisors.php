<?
include_once "config.inc";

class xero_advisors extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		
		$type = get_class();		
/*
		db::query("		
			UPDATE load_queue
			 
			 SET processing = 0

			 WHERE			 
				 url IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) < 3000 And url like 'http://www.google.com%')
				 AND type ='$type'
		");*/

		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");




*/
		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1");
		//db::query("DROP TABLE $type");	
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
//..		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='xero_advisors' ");
//		

		//$this->noProxy=true;

//
/*
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
	*/
		
		for ($i =1;$i<50;$i++)
		{
			$this->loadUrl("https://www.xero.com/content/xero/us/advisors/find-advisors/jcr:content/par/advisors_search_6526/advisorsResults.html?type=advisors&orderBy=ADVISOR_RELEVANCE&sort=DESC&pageNumber=$i&firm-size=1%2C50&view=list");
		}
	}
	
	static function parse($url,$html)
	{
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$ap = new  Address_Parser();
		$pp = new Phone_Parser();
		$ep = new Email_parser();

		$urls = array();
		// load next page links
		$xListing = new XPath($html);
		foreach ($xListing->query("//nav[@class='advisors-pagination']//a") as $node)
		{
			
			$urls[]  =  self::relative2absolute($url,$node->getAttribute("href"));
			
		}
		$thiz->loadUrlsByArray($urls);

		$data = array();


			

		foreach($xListing->query("//li[@class='advisors-list__item']") as $nodeListing)
		{
			$x = new XPath($nodeListing);	

			$data = array();
			foreach ($x->query("//h3") as $node)
			{
				$data['COMPANY_NAME'] = $node->textContent;
			}
			
			if (preg_match("/accountant/",$url))
				$data['CATEGORY'] = "Accountant";
			
			else if (preg_match("/bookkeepers/",$url))
				$data['CATEGORY'] = "Bookkeepers";
			
			else if (preg_match("/cloud-integrators/",$url))
				$data['CATEGORY'] = "Cloud Integrators/";

			
			foreach ($x->query("//dd[@class='advisors-list__address']") as $node)
			{
				$data = array_merge($data, $ap->parse ($node->textContent));
			}
			foreach ($x->query("//span[@class='advisors-list__address__container']") as $node)
			{
				
				$dr = $node->getAttribute("data-ref");
				
				if (! empty($dr))
				{					
					$data['EMAIL'] = str_replace("|","@", html_entity_decode($dr));
					break;
				}				
			}

			foreach ($x->query("//a[contains(@class,'advisors-list__phone')]") as $node)
			{
				$data['PHONE'] = self::cleanup($node->textContent);
			}
			
			
			foreach ($x->query("///dl[@class='advisors-list__contact']//a") as $node)
			{
				$data['WEBSITE'] = $node->getAttribute("href");
				break;
			}

			foreach ($x->query("//p[contains(@class,'advisors-list__description')]") as $node)
			{
				$data['BLURB'] = @Html2Text::convert($node->c14n());
			}


			$data['SOURCE_URL'] = $url;
			log::info($data);		
			db::store($type,$data,array('CATEGORY', 'COMPANY_NAME','PHONE','ZIP'));	

		}


	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='xero_advisors' and parsed=1 ");
/*db::query("DROP TABLE xero_advisors ");
*/
$r = new xero_advisors();
$r->parseCommandLine();
