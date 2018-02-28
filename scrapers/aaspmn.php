<?
include_once "config.inc";
error_reporting(E_ALL);

class aaspmn extends baseScrape
{
	public static $_this=null;
	
	public function runLoader()
	{
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		#db::query("DROP TABLE $type");
		#db::query("DELETE FROM $type");
		
		
		
		
		$this->threads=1;
		
		$this->debug=false;
		//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
				
		$this->loadUrl("http://www.aaspmn.org/mc/directory/viewallmembers.do?orgId=aaspm&masthead=true");
		$this->queuedGet();

		for($i = 2 ; $i < 54; $i++)
		{
			$this->loadUrl("http://www.aaspmn.org/mc/directory/viewResultsPageByPage.do?hidWhereTo=&updateTags=true&userId=&selectPageNav=$i&pageNumber=$i&selectPageNavBottom=2");
			$this->queuedGet();
		}
	}
	
	
	static function parse($url,$html)
	{
		
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$x = new HtmlParser($html);
		$ap = new Address_Parser();
		$oh = new Operating_Hours_Parser(); 
		$found  = false;
		
		foreach ($x->query("//table[@width='274']") as $listing)
		{
			$data=array();			

			$listingParser = new HtmlParser($listing->c14n());
			foreach($listingParser->query("//td[@width='340']//strong") as $node)
			{
				$data['Company Name'] = trim($node->textContent);
			}

			foreach($listingParser->query("//td[@width='340']//em") as $node)
			{
				$data['Owner Name'] = trim($node->textContent);
			}

			foreach($listingParser->query("//td[@width='340']//span[1]") as $node)
			{
				$contact = $node->textContent;

				// remove anything enclosed by em
				$address = preg_replace('/.+<\/em>/i',"",$node->c14n());
				$data = array_merge($data,$ap->parse($address));
			}


			foreach($listingParser->query("//td[@width='340']//span[2]") as $node)
			{
				list($k,$v)= explode(":", preg_replace("/:[^0-9]*/",":", $node->textContent));
				$data[$k] = $v;
			}

			foreach($listingParser->query("//td[@width='427']//span") as $node)
			{
				$data['Services Offered'] = $node->textContent;
			}

			foreach($listingParser->query("//td[@width='427']//a") as $node)
			{
				$data['url'] = $node->getAttribute("href");
			}

			log::info($data);			
			if ( 
					isset($data['Company Name']) && 
					isset($data['Phone']) 
				)
			{
				db::store($type,$data,array('Company Name','Phone'),false);
			}

		}
		

		//	log::info($data);
		//	db::store($type,$data,array('Name','Telephone'),false);
		
		
	}
	
	
	
}

$r = new aaspmn();
$r->runLoader();
$r->parseData();
$r->generateCSV();
