<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class veterinarian_pages extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		//$this->noProxy=false;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
	/*		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
	
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		
			
	
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	
*/	
		  $type = self::type();
      $count=0;
		$webRequests = array();
      foreach(db::onecol("SELECT distinct zip,state FROM geo.locations where pop > 30000",true) as $zip)
      {
			$webRequests[] = new WebRequest("http://www.veterinarian-pages.com/search.php?zip=$zip",$type,"POST","local_search=$zip&local_search_filter=zip&button_submit_search=Search");
		}
		$this->loadWebRequests($webRequests);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
log::info($url);
		if (strpos($url, "search.php") > 0)
		{
			/// get next page links
			$urls = array();
			foreach($x->query("//div[@id='expanded_listings']//h3//a") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			$thiz->loadUrlsByArray($urls);				
		}
		else
		{
			
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();


			$data = array();

			foreach ($x->query("//div[@id='contact']//h2") as $node)
			{
				$data['NAME'] = $node->textContent;
			}

			foreach ($x->query("//div[@id='contact']//p//strong") as $node)
			{
				$data['DOCTOR_NAME'] = $node->textContent;
			}

			foreach ($x->query("//input[@id='directions_to']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->getAttribute("value")));
			}
			
			$contacts=array();
			foreach ($x->query("//*[@id='contact']/ul/li") as $node)
			{
				$contacts[] = $node->textContent;
			}
			$data = array_merge($data,$kvp->parse($contacts));
			
			$specialties = array();
			foreach ($x->query("//div[@id='specialties_container']//li") as $node)
			{

				$specialties[]= $node->textContent;
			}
			$data['SPECIALTIES']  = join("|",$specialties);

			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			
		}
	}
}

$r= new veterinarian_pages();
$r->parseCommandLine();

