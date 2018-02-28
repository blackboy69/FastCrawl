<?
include_once "config.inc";

class footlevelers extends baseScrape
{
    public static $_this=null;
	public static $google = null;
	public static $bing = null;	
	public static $csv=null;
	public static $csvMap=array();

	private $urlsToLoad = array();
	function __construct()
	{
			$type = get_class();
		parent::__construct();

		// self::$bing = new search_engine_bing();
		//$this->nextProxyUrl = "http://hidemyass.com/proxy-list/search-225371"; // USA ONLY. required for bing results to be accurate when doing proxy jumping
		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1 ");
		//db::query("DROP TABLE $type");	
	}

	
   public function runLoader()
   {
		
		$type = get_class();		

		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		
*/
		//$this->proxy = "localhost:8888";

		$this->noProxy=true;
		

		$this->threads=1;
		$this->useCookies = false;
		$this->timeout = 15;
		$urlsToLoad = array();
		
		$urls = array();$i=0;
		foreach(db::query("SELECT address,zip from geo.address",true) as $address)
		{
			$urls[] = "http://www.footlevelers.com/professional-search/?address=".urlencode($address['address'])."&zip=".urlencode($address['zip']);
		}
		//$this->debug=true;
		$this->loadUrlsByArray($urls);
		$this->queuedGet();

	}
	public static function loadCallBack($url,$html,$type)
	{
		parent::loadCallBack($url,$html,$type);

		$query = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip			

		$t = self::getInstance();
	//	$t->sendQueryStringOnPost->false;

		//Load search page
		$searchPage = new HtmlParser($html);
		$viewstate = $searchPage->loadViewState();		
		
		$form = array();
		log::info("Pre Searching");
		//Choose Country
		$form['foot_maincenter_0$foot_maincenter_content_0$ddlCountries']='US';
		$form['foot_maintop_0$txtCriteria']='Search Site';
		$form['ucScriptManager$sm']='foot_maincenter_0$foot_maincenter_content_0$upUpdatePanel|foot_maincenter_0$foot_maincenter_content_0$ddlCountries';
		$form = array_merge($form,$viewstate);
//		$html  = $t->Post("http://www.footlevelers.com/professional-search?".$t->buildQuery($form,2));
		$html  = $t->Post("http://www.footlevelers.com/professional-search#".urlencode($query['address']).",".$query['zip'], $form);
		$chooseCountry =  new HtmlParser($html);
		$viewstate = $chooseCountry->loadViewState();	




		log::info("Searching");
		// perform search
		$form = array();
		$form['foot_maincenter_0$foot_maincenter_content_0$txtYourAddress'] = $query['address'];
		$form['foot_maincenter_0$foot_maincenter_content_0$txtZip'] = $query['zip'];
		$form['foot_maincenter_0$foot_maincenter_content_0$ddlCountries'] = 'US';
		$form['foot_maintop_0$txtCriteria'] = 'Search Site';
		$form['foot_maincenter_0$foot_maincenter_content_0$txtMeat'] = '';
		$form['ucScriptManager$sm'] = 'foot_maincenter_0$foot_maincenter_content_0$upUpdatePanel|foot_maincenter_0$foot_maincenter_content_0$btnSearch';
		$form['__ASYNCPOST'] = 'true';
		$form['foot_maincenter_0$foot_maincenter_content_0$btnSearch'] = 'Search';
		$form = array_merge($form, $viewstate);
		$html = $t->Post("http://www.footlevelers.com/professional-search", $form);


		$type = get_class();		
		$host = parse_url($url,PHP_URL_HOST);
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);	

		$data=array();           

		$ap = new Address_Parser();
		$ph = new Phone_Parser();

		$x = new XPath($html);	
		foreach($x->query("//div[@id='row']|//div[@id='altRow']") as $node)
		{
			$x2 = new XPath($node);

			$cell = array();$i=0;

			foreach($x2->query("//div") as $node2)
			{
				$cell[$i++] = $node2->c14n();

			}
			$data['Name'] = html_entity_decode( trim(strip_tags($cell[1])));
			$address = $cell[2];
			$data = array_merge($data,$ap->parse($address));
			$data = array_merge($data,$ph->parse($address));
			

			log::info($data);
			if (isset($data['Name']))
			{


				try {				
					db::store($type,$data,array('Name', 'Raw Address'));
				}
				catch(Exception $e)
				{
					log::error ("Cannot store ".$data['Name']);
					log::error($e);
					//print_r($data);
					exit;
				}		
			}
		}

		function parse($url,$html)
		{
			//do nothing
		}

	}
}
$r = new footlevelers();
$r->parseCommandLine();
