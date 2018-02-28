<?
include_once "config.inc";

class narpm_org extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' ");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
	//	db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DROP TABLE $type");
		//db::query("DELETE FROM $type");
		
		

		
		//$this->threads=2;

		$this->debug=false;
//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
		// start crawling from state list
		
			$this->LoadUrl("http://www.narpm.org/find/property-managers?submitted=true&a=managers&fname=&lname=&company=&city=&state=&chapter=&sort=rap_member.last_name&resultsperpage=50000");
   }
	

	static function parse($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		$found  = false;
		
		// parse listings now
		foreach ($x->query("//tr[not(contains(@id,'_s'))]") as $node)
		{
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);	
			$data = array();

			// only continue if we are on the right node
			foreach ($x2->query("//h2") as $aNode)
			{
				$data['NAME'] = preg_replace("/, Professional Member/", "", $aNode->textContent);
			}
			
			if (empty($data['NAME']))
				continue;

			$sisterNode=$node->nextSibling ;
			$data = array_merge($data,$ap->parse($sisterNode->c14n()));
			$data = array_merge($data,$pp->parse($sisterNode->c14n()));
			
			$babyBrotherNode = $sisterNode->nextSibling;
			$data['Chapters'] = $babyBrotherNode->textContent;

$nl =  $node->previousSibling->getElementsByTagName("td");
$i=0;
			foreach($nl as $n )
			{
				if ($i++==1)
					$data["COMPANY_NAME"] = $n->textContent;
			}

			
			
			$data['SOURCE_URL'] = $url;
			
			log::info(db::normalize($data));
			db::store($type,$data,array('NAME','ADDRESS','PHONE'),false);
			
		}
	}
}
$r = new narpm_org();
$r->parseCommandLine();


