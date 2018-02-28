<?
include_once "config.inc";

class pompstire extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type'");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1 ");

		#db::query("DELETE FROM raw_data");
		db::query("DROP TABLE IF EXISTS $type");
		#db::query("DELETE FROM $type");

		
		$this->threads=2;

		$this->debug=false;
		$this->loadUrl("http://www.pompstire.com/locations/services-by-location.aspx");
	
	}
	static function parse($url,$html)
	{		
		$thiz=self::getInstance();
		$type = get_class();			
		$x = new Xpath($html);	
		
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();


		$hrefs=array();
		foreach ($x->query("//a[contains(@href,'/locations/mode/d/locationid/')]") as $node)
		{
			$hrefs[] = $node->getAttribute("href");
		}
		if (!empty($hrefs))
		{
			$thiz->loadUrlsByArray($hrefs);
			return;
		}
		$data = array();

	
		foreach ($x->query("//*[@id='LocModWrapper']/div/p/strong") as $node)
		{
			$data['NAME'] = $node->textContent;
			break;
		}

		foreach ($x->query("//*[@id='LocModWrapper']/div/div/p[1]/text()[1]") as $node)
		{
			$data['MANAGER'] = $node->textContent;
		}


		$addr=array();
		foreach ($x->query("//*[@id='LocModWrapper']/div/div/p[1]/text()[2]") as $node)
		{
			$addr[] = $node->textContent;
		}

		foreach ($x->query("//*[@id='LocModWrapper']/div/div/p[1]/text()[3]") as $node)
		{
			$addr[] = $node->textContent;
		}

		$data = array_merge($data, $ap->parse(join(",", $addr)));
		
	
		foreach ($x->query("//div[@class='locphone']") as $node)
		{
			$data["PHONE"] = $node->textContent;
		}

		foreach ($x->query("//div[@class='locwww']") as $node)
		{
			$data["WEBSITE"] = $node->textContent;
		}
		foreach ($x->query("//div[@class='locemail']") as $node)
		{
			$data["EMAIL"] = $node->textContent;
		}

		foreach ($x->query("//div[@class='locfax']") as $node)
		{
			$data["FAX"] = $node->textContent;
		}

		$data['SOURCE_URL'] = $url;

		log::info($data);								
		db::store($type,$data,array('NAME','CITY','ADDRESS'),false);

		

	}

}
$r = new pompstire();
$r->parseCommandLine();
