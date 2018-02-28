<?
include_once "config.inc";

class AAA extends baseScrape
{
   public static $_this=null;

	public $basePath = '';

   // this function is threadsafe and can be run concurrently
   public function runLoader()
   {
		// because the static callback needs to call our parse data function.
		//db::query("DELETE FROM load_queue where type='AAA' ");
		//db::query("DELETE FROM raw_data where type='AAA' ");
		// forces a reparse...
		//db::query("UPDATE raw_data set parsed = 0 where type='AAA' ");
		#db::query("UPDATE load_queue set parsed = 0 where type='AAA' ");
		#db::query("DELETE FROM AAA");

		$this->threads=1;
		$this->useCookies=false;
//		$this->debug=true;
		$this->allowRedirects = true;
		$this->timeout = 15;
//		$this->debug=true;
//	log::$errorLevel = ERROR_ALL;
//		$this->proxy = "localhost:9666";

		$this->login();
		log::info("Loading URLs.");

		$current_zip = trim(file_get_contents("current_zip.txt"));

		$i = 0;
		foreach (db::oneCol("SELECT distinct zip FROM geo.locations where pop < 7500 and zip >= $current_zip order by zip asc") as $zip)
		{
			foreach (array('AUTOBODYDETAILING','AUTOGLASS','MECHANICAL') as $type)
			{			
				$i++;
				log::info("$zip");

				$loadUrl = "http://ww2.aaa.com$this->basePath?shopLocatorForm=shopLocatorForm&shopTypes=$type&shopStreetTextbox=&cityState=&cityState_code=&cityStateDidYouMean=&shopZipPostalTextbox=$zip&searchRadius=100&searchButton1NonReset=Search+for+Shops&makeTypeMech=All+Makes&javax.faces.ViewState=j_id1%3Aj_id2";
				
				$this->loadUrl($loadUrl);

				if ($i%$this->threads == 0)
				{
					$this->queuedPost('AAA::loadCallBack');
				}
				file_put_contents("current_zip.txt",$zip);
			}
		}
	
		$this->queuedPost('AAA::loadCallBack');		
		log::info("Parsing data.");
   }

	function login()
	{
		log::info("Attempting Login");
		$this->basePath = "";
		$html = file_get_contents("http://ww2.aaa.com/services/automotive/shopLocator/shopLocatorSearch.xhtml?club=007&association=aaa&dt=1290556525224");

		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);
		foreach($x->query("//form") as $node)
		{
			$action =  $node->getAttribute("action");
			$this->basePath = $action;
			break;
		}

		if (empty($this->basePath))
		{
			log::error("Could not login!");
			exit;
		}

		// update the database to use the new sessionID

	}
   
	static function loadCallBack($url,$html)
	{
		$type = get_class();

		if (preg_match("/sessionExpired.xhtml/",$url))
		{
			return AAA::getInstance()->login();
		}

		parent::loadCallBack($url,$html,$type);
	}

	static function parse($url,$html)
	{
		$type = get_class();

		// parse the listsings of jobs page. 
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);		
		
		foreach($x->query("//div[@class='resultRow']") as $node)
		{
			// parse the listsings of jobs page. 
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($node->c14n());
			$x2 = new DOMXPath($dom2);

			foreach($x2->query("//a[@class='bodyTextBoldLink']") as $node2)
			{
				$data['name'] = trim($node2->textContent);
			}

			$address = array();
			foreach($x2->query("//span[@class='addrLine1']") as $node2)
			{
				$address[] = trim($node2->textContent);
			}
			foreach($x2->query("//span[@class='addrLine2']") as $node2)
			{
				$address[] = trim($node2->textContent);
			}
			$data['address'] = join(" ",$address);

			foreach($x2->query("//span[@class='addrCity']") as $node2)
			{
				$data['city'] = trim($node2->textContent);
			}

			foreach($x2->query("//span[@class='addrState']") as $node2)
			{
				$data['state'] = trim($node2->textContent);
			}

			foreach($x2->query("//span[@class='adddrZip']") as $node2)
			{
				$data['zip'] = trim($node2->textContent);
			}

			foreach($x2->query("//span[@class='addrPhone']") as $node2)
			{
				$data['phone'] = trim($node2->textContent);
			}

			foreach($x2->query("//span[@class='tooltipSYCSContent']") as $node2)
			{
				$data['memberDiscount'] = strlen(trim($node2->textContent)) <1 ? 0 : 1;
			}

			log::info($data['name']);
			db::insertInto('AAA',$data,false,true);
		}

	}
}

$r = new AAA();
$r->runLoader();
$r->parseData();
$r->generateCSV();
