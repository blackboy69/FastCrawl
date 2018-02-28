<?
include_once "config.inc";
//R::freeze();

class stanjohnsonco_com extends baseScrape
{
    public static $_this=null;
   //public $postContentType = "Content-Type: text/xml";
   public function runLoader()
   {
		$type = get_class();		
	/*			
			$this->noProxy=false;
		$this->proxy = "localhost:8888";

			
			

			$this->useCookies=false;
			$this->allowRedirects = true;
			
			$this->debug=false;
		*/	
		//db::query("DELETE FROM LOAD_QUEUE WHERE type = '$type' and url like 'localhost%'");
		//db::query("DELETE FROM RAW_DATA WHERE type = '$type'");
		//$this->clean();
		$this->threads=10;		
		
		for($i=1;$i<25;$i++)
		{
			$this->loadUrl("https://www.stanjohnsonco.com/our-people?field_first_name_value=&field_last_name_value=&field_address_locality=&page=$i");
		}		
	}
	

	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$urls = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 
		
		$data = array();
		$x =  new  XPath($html);			
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
			
		foreach($x->query("//a[contains(@href,'/our-brokers/')]") as $node)
		{
			$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
		}		
		log::info($urls);
		if (sizeof($urls)>0)	
		{
			$thiz->loadUrlsByArray($urls);		
		}
  
		foreach($x->query("//div[@class='kw-userAddresscontainer']//p[contains(@class,'kw-boldText')]") as $node)
		{
			$data = array_merge($data, $np->parse($node->textContent));
		}	
		
		foreach($x->query("//div[@class='kw-userAddresscontainer']//p[1]") as $node)
		{
			$data['TITLE'] = trim($node->textContent);
		}
		
		foreach($x->query("//div[@class='kw-Address']") as $node)
		{		
			$data = array_merge($data, $ep->parse($node->c14n()));
			$data = array_merge($data, $pp->parse($node->c14n()));
			$data = array_merge($data, $ap->parse($node->c14n()));
		}
		
		foreach ($x->query("//fieldset") as $node)
		{	
			$x2= new Xpath($node);
			foreach($x2->query("//legend") as $node2)
			{
				$key = trim($node2->textContent);
			}
			
			foreach($x2->query("//div") as $node2)
			{
				$value = trim($node2->textContent);
			}
			
			if (!empty($key) && !empty($value) )
				$data[$key]= $value;
			
		}
		
		$data = db::normalize($data);			
			
		$data["SOURCE URL"] = $url;
		log::info($data);			
		
		
		db::store($type,$data,array('FIRST_NAME','LAST_NAME','ADDRESS','CITY'),true);		
	
	}
}

$r= new stanjohnsonco_com();
$r->parseCommandLine();

