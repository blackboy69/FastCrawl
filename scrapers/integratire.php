<?
include_once "config.inc";

class integratire extends baseScrape
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
		//$this->loadUrl("http://1699airlinehighway.integratiresanfrancisco.com/store.aspx?shopNum=1167&language=en-US");
		$this->loadUrl("http://integratire.com/directory/listing/");
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
		foreach ($x->query("//div[@class='sabai-directory-title']//a") as $node)
		{
			$hrefs[] = $node->getAttribute("href");
		}

		foreach ($x->query("//a[text()='Next']") as $node)
		{
			$hrefs[] = $node->getAttribute("href");
		}
		if (!empty($hrefs))
		{
			$thiz->loadUrlsByArray($hrefs);		
		}

		$data = array();
		

	
		foreach ($x->query("//*[@class='page-title']") as $node)
		{
			$data['Name'] = $node->textContent;
		}

if ($data['Name'] =="Directory") return;
		foreach ($x->query("//*[@class='sabai-directory-address']") as $node)
		{
			$data = array_merge($data, $ap->parse($node->textContent));
		}

		$data = array_merge($data, $pp->parse($html));

		foreach ($x->query("//div[@class='sabai-directory-email']") as $node)
		{
			$data["EMAIL"] =$node->textContent;
		}


		$data['SOURCE_URL'] = $url;

		log::info($data);								
		db::store($type,$data,array('NAME','CITY','ADDRESS'),false);

		

	}

}
$r = new integratire();
$r->parseCommandLine();
