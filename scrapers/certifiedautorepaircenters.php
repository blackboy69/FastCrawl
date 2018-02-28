<?
include_once "config.inc";

class certifiedautorepaircenters extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
//		$this->proxy = "localhost:8888";

		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;

	/*	
		db::query("UPDATE load_queue set processing = 0 where type='certifiedautorepaircenters' and processing = 1 ");		
		db::query("UPDATE raw_data set parsed = 0 where type='certifiedautorepaircenters' and parsed = 1  ");
		
				
				db::query("UPDATE raw_data set parsed = 0 where type='certifiedautorepaircenters' and parsed = 1  and url like 'http://www.certifiedautorepaircenters.com/doctors/Dr_% ");
				db::query("DELETE FROM LOAD_QUEUE where type='certifiedautorepaircenters' and url like 'http://www.certifiedautorepaircenters.com/doctors/Dr_%' ");



		db::query("DELETE FROM raw_data where type='certifiedautorepaircenters' and url like '%shop.do?SHOPID=%'");			
		db::query("DELETE FROM load_queue where type='certifiedautorepaircenters'");

		exit;*/
		db::query("DROP TABLE certifiedautorepaircenters");
				db::query("UPDATE raw_data set parsed = 0 where type='certifiedautorepaircenters' and parsed = 1  ");

		$this->loadUrlsByCity("http://www.certifiedautorepaircenters.com/CertAutoRepair/guest/form/detailShopSearch/submit.do?RADIUS=100&STREET=&CITY=%CITY%&STATE=%STATE%&ZIP_CODE=&action=execute");

		$this->queuedFetch();

	}

	static function loadCallBack($url,$html,$arg3)
	{

		if (strlen($html) > 0 )
		{
			baseScrape::loadCallBack($url,$html,$arg3);
		}		
		else
		{
			log::info("Empty, skipping $url");
		}
		
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	


		if (preg_match("/detailShopSearch/",$url))
		{

				$urls = array();
			// load listings
			foreach ($x->query("//a[contains(@href,'shop.do?SHOPID=')]") as $node)

			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			$data = array();

			$kvp = new KeyValue_parser();
			$ap = new Address_Parser();
			$pp = new Phone_Parser();
			$op=new Operating_Hours_Parser();


			foreach($x->query("//div[@id='content']//h2") as $node)
			{
				$data['NAME']=$node->textContent;
			}
			
			foreach($x->query("//div[@id='content']//h2/following-sibling::p[1]") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
				$data = array_merge($data,$pp->parse($node->c14n()));
			}

			foreach($x->query("//p[@class='links']//a[@target='_blank']") as $node)
			{
				$data['WEB_SITE']=$node->getAttribute("href");;
			}
				
			$services = array();
			foreach($x->query("//div[@id='content']//h3[contains(text(),'Services Offered')]/following-sibling::p//b") as $node)
			{
				$services[] = self::cleanup( $node->textContent );
			}
			if (!empty($services))
				$data['SERVICES_OFFERED']=join(", ", $services);

			$staff = array();
			foreach($x->query("//div[@id='content']//h3[contains(text(),'Our Staff')]/following-sibling::p") as $node)
			{
				$staff[] = self::cleanup( $node->c14n() );
			}
			if (!empty($staff))
				$data['STAFF_INFO']=join(", ", $staff);

			$hours = array();
			foreach($x->query("//div[@id='content']//h3[contains(text(),'Hours of Operation')]/following-sibling::p") as $node)
			{
				$hours[] = self::cleanup( $node->textContent );
			}
			if (!empty($hours))
				$data = array_merge($data,$op->parse($hours));


			if (!empty($data['NAME']))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);					
				db::store($type,$data,array('NAME','PHONE','ADDRESS'));	
			}
			else
				log::info("$url\nNO DATA");
		}
	}
}
$r= new certifiedautorepaircenters();
$r->parseCommandLine();

