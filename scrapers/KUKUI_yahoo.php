<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

/*
http://www.ip-address.org/reverse-lookup/reverse-ip.php
http://www.yougetsignal.com/tools/web-sites-on-web-server/
*/
class kukui extends baseScrape
{
    public static $_this=null;
	
	function __construct()
	{
		parent::__construct();

		$this->bing = new search_engine_bing();
		$this->google = new search_engine_google();
		$this->yahoo = new search_engine_yahoo();
	}


   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		$this->maxRetries   = 0;
		$this->sleepInterval= 2; 
//		$this->retryEnabled=false;
		//db::query("DROP TABLE $type");	
	//	db::query("UPDATE raw_data set parsed = 0 where type='KUKUI'");

		/*db::query("UPDATE raw_data set parsed = 0 where type='KUKUI' and parsed = 1 ");
		db::query("DROP TABLE $type");
	
		
		
		
		db::query("UPDATE load_queue set processing = 0 where type='KUKUI' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='KUKUI' and parsed = 1 ");	

	*/	
		//db::query("DELETE FROM load_queue where type='KUKUI' ");
		//db::query("DELETE FROM raw_data where type='KUKUI' ");

		
		$this->loadUrl($this->yahoo->url('"website by KUKUI"'));
		$this->loadUrl($this->bing->url('"website by KUKUI"'));
		$this->loadUrlsByArray(self::allTheLinks());
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$host = parse_url($url,PHP_URL_HOST);

		$listings=array();
		if (preg_match("/yahoo/",$url))
			$listings = $thiz->yahoo->parse($html,true,true);
		else if(preg_match("/bing/",$url))			
			$listings = $thiz->bing->parse($html,true,true);
		else
			log::error("Unknown url $url");
log::info($url);
		foreach($listings as $listing)
		{
print_R($listing);
			$lurl = $thiz->relative2absolute($url,$listing['URL']);
			$data = array();
			$data['SITE'] = parse_url($lurl,PHP_URL_HOST);
			$data['TITLE'] = $listing['TITLE'];
			$data['SOURCE_URL'] = $listing['URL'];
			$data['SEARCH_URL'] = $url;
			
			if (preg_match("/bing|yahoo/",$data['SITE']))
			{
				log::info("loading {$listing['URL']}");
				$thiz->loadUrl($listing['URL']);
			}
			else
			{
				db::store($type,$data , array('SITE'));
			}
		
		}
	}

	public static function allTheLinks()
	{
		return array("http://www.ALOHAMEDICALCENTER.COM", "http://www.ALTITUDE21C.COM", "http://www.ANAHOLABEACHHOUSE.COM", "http://www.ANDERSONAUTOMOTIVE.NET", "http://www.ARIZONAIMAGING.COM", "http://www.ATTYDC.COM", "http://www.AZIMAGING.NET", "http://www.AZIMAGING360TOURS.COM", "http://www.BIGISLANDREALESTATEREPORT.COM", "http://www.CALIPJOPROPERTIES.NET", "http://www.CASHINZONE.COM", "http://www.CHOOSEWINDWARDNISSAN.COM", "http://www.COGENTD.COM", "http://www.COROASLAVS.COMORKEY", "http://www.CURTISLAWCONSTRUCTION.COM", "http://www.DEJESUSARCHITECTURE.COM", "http://www.DHPTRADE.COM", "http://www.DMPHAWAII.COM", "http://www.EVERESTHOLDINGS.NET", "http://www.EXPLOREIN3D.COM", "http://www.FISHOILPLUS.COM", "http://www.FISHOILSALES.COM", "http://www.FREMONTAUTOBODYSHOP.COM", "http://www.HAWAII-HOMECARE.COM", "http://www.HAWAIICAREANDCLEANING.COM", "http://www.HAWAIICHILDRENSTHEATRE.COM", "http://www.HAWAIIIMAGING.COM", "http://www.HAWAIIPACIFICPARADISE.COM", "http://www.HAWAIIREALE.COM", "http://www.HAWAIISIR.COM", "http://www.HAYWARDAUTOSERVICE.COM", "http://www.HIFSBO.COM", "http://www.HIVACATIONS.COM", "http://www.HOME-BUSINESS-ZONE.COM", "http://www.IPIXHAWAII.COM", "http://www.ISLANDPACIFICMORTGAGE.COM", "http://www.JAIANANDA.COM", "http://www.JOY-ORTIZ-ZIMMER.COM", "http://www.KAIWACONSTRUCTION.COM", "http://www.KAUAICONSTRUCTIONSERVICES.COM", "http://www.KAUAIFOREIGNCARS.COM", "http://www.KCSUSA.COM", "http://www.KEALANANI.COM", "http://www.KEAWECONCRETEPRODUCTS.COM", "http://www.KEVINCROSSE.COM", "http://www.KFOREIGNCARS.COM", "http://www.KILAUEALIGHTHOUSEVILLAGE.COM", "http://www.KINGAUTOCENTER.COM", "http://www.KINGWINDWARD.COM", "http://www.KUAI720AM.COM", "http://www.KUKUI.COM", "http://www.KUKUIIT.COM", "http://www.KYMRY.COM", "http://www.KYMRYPEREZ.COM", "http://www.LETSGOHAWAII.COM", "http://www.LISTSAFELY.COM", "http://www.MACARTHURANDCOMPANY.COM", "http://www.MACARTHURHAWAII.COM", "http://www.MACARTHURHAWAIISIR.COM", "http://www.MAUIREALE.COM", "http://www.NAILERSTOOLBELTS.COM", "http://www.NIKLBAG.COM", "http://www.OBERGFREE.COM", "http://www.OMEGA3-FISH-OIL.COM", "http://www.OMEGA3FISHOIL.NET", "http://www.ONARABAND.COM", "http://www.PACIFICMOTORSERVICE.COM", "http://www.PATTYCHANEY.COM", "http://www.PENNYSBOOK.COM", "http://www.PKYNO.COM", "http://www.PREMIERHAWAIIREALESTATE.COM", "http://www.PREMIUM-CAT-FOOD.COM", "http://www.PREMIUM-DOG-FOOD.COM", "http://www.PUNINANIPOOLS.COM", "http://www.RANKINGMONKEY.COM", "http://www.RANKINGSMONKEY.COM", "http://www.REAL-ESTATE-GUIDE-KAUAI.COM", "http://www.RFERRARO.COM", "http://www.RONRAMIESINC.COM", "http://www.RUSSELLKYONO.COM", "http://www.SAFARIAVIATIONINC.COM", "http://www.SAFARIAVIATIONS.COM", "http://www.SAFARIHELICOPTERS.COM", "http://www.SANJOSESAUTOSERVICE.COM", "http://www.SANJOSESAUTOSERVICES.COM", "http://www.SARATOGASHELL.COM", "http://www.SHAKA103.COM", "http://www.SLEEPINGGIANT.COM", "http://www.SOLVSIMVIA.COMORKEY", "http://www.SURF959FM.COM", "http://www.SUSANLMARSHALL.COM", "http://www.THEULTIMATEFISHOIL.COM", "http://www.THOMASMCINERNEY.COM", "http://www.VITAMUNDO.COM", "http://www.WHEELCHAIR-KAUAI.COM", "http://www.WOOSTERLLC.COM", "http://www.ZONE-PRODUCTS.COM", "http://www.ZONEANDTONE.COM");
	}
}

$r= new KUKUI();
$r->parseCommandLine();

