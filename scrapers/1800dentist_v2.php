<?
include_once "config.inc";


class one800dentist extends baseScrape
{
	public static $_this=null;
	public $isPost = true;

	public function runLoader()
	{
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' ");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DROP TABLE $type");
		//db::query("DELETE FROM $type");
		
		$this->threads=6;
		$this->cookie_file= "cookie.$type.txt";
		
		$this->debug=false;
		//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";	
		//$this->noProxy = false;

		$this->useCookies=true;
	
			
		/*db::query("DELETE FROM load_queue where type='$type' ");
		db::query("DELETE FROM raw_data where type='$type' ");*/
		//db::query("UPDATE raw_data set parsed = 0 where type='one800dentist' ");
		$this->loadUrl("http://www.1800dentist.com/");
		
	}

	public function parse($url,$html)
   {
      
      $type =get_class();    

		log::info("Parsing $url");

		$ap = new Address_parser();
		$kvp = new KeyValue_Parser();
		$x = new Xpath($html);
		$thiz = self::getInstance();
		

			$links = array();
			foreach ($x->query("//a[contains(@id,'lnkCity')]") as $node)
			{
			  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			foreach ($x->query("//a[contains(@id,'lnkState')]") as $node)
			{
					  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}		
			foreach ($x->query("//a[contains(@id,'lnkmemberoffices')]") as $node)
			{
					  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}	
			foreach ($x->query("//a[contains(@id,'lnkmemberpractice')]") as $node)
			{
					  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}		
			
			foreach ($x->query("//a[contains(@id,'lnkalldentists')]") as $node)
			{
					  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}		

			foreach ($x->query("//a[contains(@class,'dentistButton')]") as $node)
			{
					  $links[] = self::relative2absolute($url, $node->getAttribute("href"));
			}	
			
			$thiz->loadUrlsByArray($links);
		

		$data = array();
		foreach($x->query("//div[@class = 'addressPan']//h2") as $node)
		{
			$data['name']=$node->textContent;
		}

		if (isset($data['name']))
		{
			// dentists in format http://www.1800dentist.com/dentists/cherry-hill-dental-center/
			foreach($x->query("//div[@class = 'addressPan']//p") as $node)
			{
				$data=array_merge($data, $ap->parse($node->textContent));
			}
	
			foreach($x->query("//div[@class = 'addressPan']//strong") as $node)
			{
				$data=array_merge($data, $kvp->parse($node->textContent));
			}
		}
		else
		{
			$data ['Reviews'] = 1;

			foreach($x->query("//span[contains(@id,'lblCompany')]") as $node)
			{
				$data['name']=$node->textContent;
			}

			foreach($x->query("//span[contains(@id,'lblTFN')]") as $node)
			{
				$data['phone']=$node->textContent;
			}

			foreach($x->query("//span[contains(@id,'lblAddress')]") as $node)
			{
				$data=array_merge($data, $ap->parse($node->c14n()));
			}
		}   
		
		if (!empty($data['name']))
		{
			log::info($data);
			db::store($type,$data,array('NAME','PHONE'));
		}
	
	}	
 
	
}

$r = new one800dentist();
$r->parseCommandLine();
