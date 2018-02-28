<?
include_once "config.inc";

class oilcanhenrys extends baseScrape
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
		$this->loadUrl("http://www.oilcanhenrys.com/locations");
	
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
		foreach ($x->query("//div[@class='centerList']//a") as $node)
		{
			$hrefs[] = self::relative2absolute($url, $node->getAttribute("href"));
		}
		if (!empty($hrefs))
		{
			$thiz->loadUrlsByArray($hrefs);
			return;
		}
		$data = array();

	
		// address
		foreach ($x->query("//p[@itemtype='http://schema.org/PostalAddress']") as $node)
		{
			$data = array_merge($data, $ap->parse($node->textContent));
		}

		// phone
		foreach ($x->query("//span[@itemtype='telephone']") as $node)
		{
			$data = array_merge($data, $ap->parse($node->textContent));
		}

		$data['NAME'] = "Oil Can Henrys ".$data['CITY'];		
		

		$data['SOURCE_URL'] = $url;

		log::info($data);								
		db::store($type,$data,array('NAME','CITY','ADDRESS'),false);

	}

}
$r = new oilcanhenrys();
$r->parseCommandLine();
