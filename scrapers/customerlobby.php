<?
include_once "config.inc";

class customerlobby extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
//		$this->proxy = "localhost:8888";

		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;

	/*	
		db::query("UPDATE load_queue set processing = 0 where type='customerlobby' and processing = 1 ");		
		db::query("UPDATE raw_data set parsed = 0 where type='customerlobby' and parsed = 1  ");
		
				
				db::query("UPDATE raw_data set parsed = 0 where type='customerlobby' and parsed = 1  and url like 'http://www.customerlobby.com/doctors/Dr_% ");
				db::query("DELETE FROM LOAD_QUEUE where type='customerlobby' and url like 'http://www.customerlobby.com/doctors/Dr_%' ");



		db::query("DELETE FROM raw_data where type='customerlobby'");			
		db::query("DELETE FROM load_queue where type='customerlobby'");");


		db::query("DROP TABLE customerlobby");
		db::query("UPDATE raw_data set parsed = 0 where type='customerlobby' and parsed = 1  ");
		exit;*/
		for($i=100;$i<=12000;$i++)
			$urls[] = "https://www.customerlobby.com/reviews/$i/asdf";

		$this->loadUrlsByArray($urls);
		$this->queuedFetch();
	}

	/*static function loadCallBack($url,$html,$arg3)
	{

		if (strlen($html) > 0 )
		{
			baseScrape::loadCallBack($url,$html,$arg3);
		}		
		else
		{
			log::info("Empty, skipping $url");
		}
		
	}*/

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$data = array();

		foreach($x->query("//h1[@class='name']") as $node)
		{
			$data['NAME']=$node->textContent;
		}
		if (empty($data['NAME'])) return;


		$kvp = new KeyValue_parser();
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		

		
		foreach($x->query("//ul[@itemprop='address']") as $node)
		{
			$data = array_merge($data,$ap->parse($node->c14n()));
			$data = array_merge($data,$pp->parse($node->c14n()));
		}


		foreach($x->query("//a[@id='company-website']") as $node)
		{
			$data['WEB_SITE']=$node->getAttribute('href');
		}		
	

		foreach($x->query("//span[@itemprop='description']") as $node)
		{
			$data['BUSINESS_DESCRIPTION']=$node->textContent;
		}
		
		$cat=array();
		foreach($x->query("//li[@class='categories-list']") as $node)
		{
			$cat[] = $node->textContent;

		}
		$data['CATEGORIES']= join(", ",$cat);

		foreach($x->query("//span[@itemprop='reviewCount']") as $node)
		{
			$data['NUM_REVIEWS']=$node->textContent;
		}

		foreach($x->query("//span[@itemprop='ratingValue']") as $node)
		{
			$data['AVG_RATING']=$node->getAttribute('content');
		}

		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;
			log::info($data);					
			db::store($type,$data,array('NAME','PHONE','ADDRESS'));	
		}
	}
}
$r= new customerlobby();
$r->parseCommandLine();

