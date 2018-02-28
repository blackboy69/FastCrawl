<?
include_once "config.inc";

class medfusion extends baseScrape
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
		db::query("UPDATE load_queue set processing = 0 where type='medfusion' and processing = 1 ");		
		db::query("UPDATE raw_data set parsed = 0 where type='medfusion' and parsed = 1  ");
		
				
				db::query("UPDATE raw_data set parsed = 0 where type='medfusion' and parsed = 1  and url like 'http://www.medfusion.com/doctors/Dr_% ");
				db::query("DELETE FROM LOAD_QUEUE where type='medfusion' and url like 'http://www.medfusion.com/doctors/Dr_%' ");


		db::query("DROP TABLE medfusion");
		db::query("DELETE FROM raw_data where type='medfusion'");			
		db::query("DELETE FROM load_queue where type='medfusion'");

		db::query("DELETE FROM raw_data where type='medfusion'");			
		db::query("DELETE FROM load_queue where type='medfusion'");	*/

		for($i=1;$i<=12000;$i++)
			$urls[] = "https://www.medfusion.net/secure/portal/index.cfm?fuseaction=home.login&dest=welcome&gid=$i";

		$this->loadUrlsByArray($urls);
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
		$data = array();
		$kvp = new KeyValue_parser();
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		

		foreach($x->query("//title") as $node)
		{
			$data['NAME']=$node->textContent;
		}
		foreach($x->query("//a[contains(text(),'Home')]") as $node)
		{
			$data['WEB_SITE']=$node->getAttribute('href');
		}

		if (empty($data['WEB_SITE']))
		{
			foreach($x->query("//a/img[contains(@src,'home')]") as $node)
			{
				$data['WEB_SITE']=$node->parentNode->getAttribute('href');
			}		
		}


		if (!empty($data))
		{
			$data['SOURCE_URL'] = $url;
			log::info($data);					
			db::store($type,$data,array('NAME','WEB_SITE'),true);	
			return;
		}
	}
}
$r= new medfusion();
$r->parseCommandLine();

