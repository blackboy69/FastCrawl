<?
include_once "config.inc";

class expressoil extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type'");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		#db::query("DELETE FROM raw_data");
		db::query("DROP TABLE  $type");
		#db::query("DELETE FROM $type");

		
		$this->threads=2;

		$this->debug=false;
		//$this->loadUrl("http://1699airlinehighway.expressoilsanfrancisco.com/store.aspx?shopNum=1167&language=en-US");
		$this->loadUrlsByState("https://www.expressoil.com/locations/?state=%STATE%");
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
		foreach ($x->query("//a[contains(@href,'/about-us/')]") as $node)
		{
			$hrefs[] = $node->getAttribute("href");
		}
		if (!empty($hrefs))
		{
			$thiz->loadUrlsByArray($hrefs);
		
		}

		$data = array();

	
		foreach ($x->query("//div[@class='location-name']") as $node)
		{
			$data['Name'] = $node->textContent;
		}

		$addr=array();
		foreach ($x->query("//div[@class='location-csz']") as $node)
		{
			$addr[] = $node->textContent;
		}

		foreach ($x->query("//div[@class='location-address']") as $node)
		{
			$addr[] = $node->textContent;
		}

		$data = array_merge($data, $ap->parse(join(",", $addr)));
		
	
		foreach ($x->query("//div[@class='location-phone']") as $node)
		{
			$data = array_merge($data, $pp->parse($node->textContent));
		}

		foreach ($x->query("//div[@class='location-fax']") as $node)
		{
			$data["FAX"] = $node->textContent;
		}

		foreach ($x->query("//div[@class='location-contact-name']") as $node)
		{
			$data["MANAGER"] =$node->textContent;
		}


		$data['SOURCE_URL'] = $url;

		log::info($data);								
		db::store($type,$data,array('NAME','CITY','ADDRESS'),false);

		

	}

}
$r = new expressoil();
$r->parseCommandLine();
