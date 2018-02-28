<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class qualsight extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=8;
		$this->debug=false;
		
		//
	/*			
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			

		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='qualsight' and parsed = 1 ");
	*/
#		db::query("	DROP TABLE qualsight"); 
#		db::query("UPDATE raw_data set parsed = 0 where type='qualsight' and parsed = 1 ");

		$this->loadUrlsByLocation("http://www.qualsight.com/lasik-doctors?zip=$ZIP&dist=100",500);
	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		if (preg_match("/all-spas/",$url))
		{
			$urls = array();
			// load listings
			foreach ($x->query("//a[@id='spa']") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			
			// load next pages
			foreach ($x->query("//div[@class='pagination-content']//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$data = array();
			foreach ($x->query("//h1[contains(@class,'columns')]") as $node)
			{
				$data['NAME'] =$node->textContent;
			}

			foreach ($x->query("//address") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
			}

			foreach ($x->query("//div[@class='property-overview_contact']") as $node)
			{
				$data = array_merge($data,$pp->parse($node->c14n()));
			}
			
			foreach ($x->query("//div[@class='property-overview_contact']") as $node)
			{
				$data = array_merge($data,$ep->parse($node->c14n()));
			}

			foreach ($x->query("//div[@class='property-overview_contact']//a") as $node)
			{
				$u = "http://test.com".$node->getAttribute("href");
				parse_str(parse_url( $u,PHP_URL_QUERY),$d);
				$data['WEB_SITE'] =  $d['ulink'];
			}

			foreach ($x->query("//a[contains(@href,'#reviews')]//span") as $node)
			{
				$data['NUM_REVIEWS'] = preg_replace("/[^0-9]/","", $node->textContent);
			}

			foreach ($x->query("//div[@class='stars-gold']") as $node)
			{
				$data['AVG_RATING'] = preg_replace("/[^0-9.]/","",$node->getAttribute("style"));
			}

			$data['GIFT_CARDS'] ='';
			foreach ($x->query("//div[@class='gift-cards-text']") as $node)
			{
				$data['GIFT_CARDS'] = $node->textContent;
			}			
		
			if (!empty($data['NAME']))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('NAME','PHONE','ZIP'));	
			}
		
		}

	

	}
}

$r= new qualsight();
$r->parseCommandLine();

