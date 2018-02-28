<?
include_once "config.inc";

class autovitals extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
	/*	

		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");

			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	*/
#db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
#db::query("DROP TABLE $type");
		for($i=0;$i<5000;$i++)
		{
			$urls[] = "http://www.autovitals.com/Reviews/$i";
		}
		$this->loadUrlsByArray($urls);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$data = array();
		$kvp = new KeyValue_parser();
		
		foreach($x->query("//span[@id='MainContentPlaceHolder__shopNameLabel']") as $node)
		{
			$data["NAME"] = $node->textContent;
		}

		foreach($x->query("//ul[@class='adr']//span") as $node)
		{
			$name = $node->getAttribute("class");
			if ( empty($name) ) $name = 'TEL';
			$data[$name] = $node->textContent;
		}

		foreach($x->query("//a[@class='url']") as $node)
		{
			$data['WEBSITE'] = $node->getAttribute('href');
		}

		foreach($x->query("//span[@id='MainContentPlaceHolder_TestimonialCountLabel']") as $node)
		{
			$data['NUM_REVIEWS'] = $node->textContent;
		}

		foreach($x->query("//span[@id='MainContentPlaceHolder__averageRatingSpan']") as $node)
		{
			$data['AVERAGE_REVIEW'] = $node->textContent;
		}
		$data = db::normalize($data);
		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;
			log::info($data);					
			db::store($type,$data,array('NAME','STREET_ADDRESS'),true);	
		}
		else
		{
			log::error("Not Found");
			log::error($url);
		}
	}
}
$r= new autovitals();
$r->parseCommandLine();

