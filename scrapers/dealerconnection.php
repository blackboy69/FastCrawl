<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class dealerconnection extends baseScrape
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
	*/
		db::query("DROP TABLE $type");	
		db::query("UPDATE raw_data set processing = 0 where type='$type' and parsed = 1 ");

		$this->loadUrl("http://content.dealerconnection.com/vfs/brands/us_ford_en.html");
		}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$x = new HtmlParser($html);	
		$op = new Operating_Hours_Parser();
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();

		$urls = array();
		foreach ($x->query("//ul[@class='listing']//a") as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		$thiz->loadUrlsByArray($urls);

		foreach ($x->query("//div[@class='dealerListing']") as $node)
		{
			$data = array();
			$x2 = new Xpath($node);	
			foreach ($x2->query("//div[@class='dealerName']//a") as $node2)
			{
				$data['NAME'] = $node2->textContent;
				$data['WEBSITE'] = $node2->getAttribute("href");
			}
			foreach ($x2->query("//div[@class='dealerAddress']") as $node2)
			{
				$data = array_merge($data,$ap->parse($node2->c14n()));
			}

			$phoneTokens = array();
			foreach ($x2->query("//div[@class='dealerPhone']//label") as $node2)
			{
				$phoneTokens[] = $node2->textContent;				
			}
			
			$tok = array();
			for($i=1; $i<sizeof($phoneTokens); $i+=2)
			{
				$tok[]= $phoneTokens[($i-1)] . $phoneTokens[$i];
			}
			$data = array_merge($data,$kvp->parse($tok));

			if (!empty($data['NAME']))
			{
				log::info($data);		
				db::store($type,$data,array('NAME','PHONE','ZIP'));	
			}
		}

	

	}
}

$r= new dealerconnection();
$r->parseCommandLine();

