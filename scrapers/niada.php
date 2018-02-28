<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class niada extends baseScrape
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
		db::query("UPDATE raw_data set processing = 0 where type='niada' and parsed = 1 ");

		db::query("	DROP TABLE niada"); 
		db::query("UPDATE raw_data set parsed = 0 where type='niada' and parsed = 1 ");
	*/

		foreach(db::onecol("SELECT distinct name from geo.states") as $statename)
		{
			$urls[] = "http://www.niada.com/member_directory.php?state=".urlencode($statename);
		}	

		$this->loadUrlsByArray($urls);

	//db::query("UPDATE load_queue set processing = 0 where type='niada' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='niada' and parsed = 1 ");
		db::query("DROP TABLE $type");	
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();

		// load next links
		foreach ($x->query("//a[@class='te_paging te_next_icon']") as $node)
		{
			$thiz->loadUrl(self::relative2absolute($url, $node->getAttribute("href")));
		}

		foreach ($x->query("//tr[@id]") as $node)
		{
			$x2 = new Xpath($node);
			$data = array();


			foreach ($x2->query("//td[1]") as $node2)
			{
				$data['CMD'] = trim($node2->textContent);
			}

			foreach ($x2->query("//td[2]") as $node2)
			{
				$data['NAME'] = $node2->textContent;
			}

			foreach ($x2->query("//td[3]") as $node2)
			{
				// ADDRESS
				$data  = array_merge($data,$ap->parse($node2->textContent));
			}

			foreach ($x2->query("//td[4]") as $node2)
			{
				$data['PHONE'] = $node2->textContent;
			}

			foreach ($x2->query("//td[2]//a") as $node2)
			{
				$data['WEBSITE'] =$node2->getAttribute("href");
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

$r= new niada();
$r->parseCommandLine();

