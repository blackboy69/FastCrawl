<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

/*
http://www.ip-address.org/reverse-lookup/reverse-ip.php
http://www.yougetsignal.com/tools/web-sites-on-web-server/
*/
class kukui_listings extends baseScrape
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
		$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		$this->maxRetries   = 0;
		$this->sleepInterval= 2; 
//		$this->retryEnabled=false;
		//db::query("DROP TABLE $type");	
		//db::query("DELETE FROM load_queue where type='kukui_listings' ");
		//db::query("DELETE FROM raw_data where type='kukui_listings' ");
		//db::query("UPDATE raw_data set parsed = 0 where type='kukui_listings'");

		/*db::query("UPDATE raw_data set parsed = 0 where type='kukui_listings' and parsed = 1 ");
		db::query("DROP TABLE $type");
	
		
		
		
		db::query("UPDATE load_queue set processing = 0 where type='kukui_listings' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='kukui_listings' and parsed = 1 ");	



		*/	
		db::query("UPDATE load_queue set processing = 0 where type='kukui_listings' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='kukui_listings' and parsed = 1 ");	
		//$this->loadUrl($this->yahoo->url('"website by KUKUI"'));
		//$this->loadUrl($this->bing->url('"website by KUKUI"'));	
		//$this->loadUrlsByArray(self::allTheLinks());
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

		// grab the directions link
$data= array();
		foreach ($x->query("//a[contains(text(),'DIRECTIONS')]") as $node)
		{
			$href = $thiz->relative2absolute($url, $node->getAttribute('href'));
			$thiz->loadUrl($href);
		}

		foreach ($x->query("//*[contains(@name,'DirectionsCompanyName')]") as $node)
		{
			$data['CompanyName'] = $node->getAttribute("value");
		}
		
		foreach ($x->query("//*[contains(@name,'DirectionsPhone')]") as $node)
		{
			$data['Phone'] = $node->getAttribute("value");
		}
		
		foreach ($x->query("//*[contains(@name,'hdnDirectionsAddress')]") as $node)
		{
			$data = array_merge($data, $ap->parse($node->getAttribute("value")));
		}

		if (isset($data['CompanyName'] ))
		{
			$data['SOURCE_URL'] = $url;
			print_r($data);
			db::store($type,$data , array('SOURCE_URL'));
		}
	
	}

	public static function allTheLinks()
	{
		return array("http://www.ALOHAMEDICALCENTER.COM/Directions","http://www.ALTITUDE21C.COM/Directions","http://www.ANAHOLABEACHHOUSE.COM/Directions","http://www.ANDERSONAUTOMOTIVE.NET/Directions","http://www.ARIZONAIMAGING.COM/Directions","http://www.ATTYDC.COM/Directions","http://www.AZIMAGING.NET/Directions","http://www.AZIMAGING360TOURS.COM/Directions","http://www.BIGISLANDREALESTATEREPORT.COM/Directions","http://www.CALIPJOPROPERTIES.NET/Directions","http://www.CASHINZONE.COM/Directions","http://www.CHOOSEWINDWARDNISSAN.COM/Directions","http://www.COGENTD.COM/Directions","http://www.COROASLAVS.COMORKEY/Directions","http://www.CURTISLAWCONSTRUCTION.COM/Directions","http://www.DEJESUSARCHITECTURE.COM/Directions","http://www.DHPTRADE.COM/Directions","http://www.DMPHAWAII.COM/Directions","http://www.EVERESTHOLDINGS.NET/Directions","http://www.EXPLOREIN3D.COM/Directions","http://www.FISHOILPLUS.COM/Directions","http://www.FISHOILSALES.COM/Directions","http://www.FREMONTAUTOBODYSHOP.COM/Directions","http://www.HAWAII-HOMECARE.COM/Directions","http://www.HAWAIICAREANDCLEANING.COM/Directions","http://www.HAWAIICHILDRENSTHEATRE.COM/Directions","http://www.HAWAIIIMAGING.COM/Directions","http://www.HAWAIIPACIFICPARADISE.COM/Directions","http://www.HAWAIIREALE.COM/Directions","http://www.HAWAIISIR.COM/Directions","http://www.HAYWARDAUTOSERVICE.COM/Directions","http://www.HIFSBO.COM/Directions","http://www.HIVACATIONS.COM/Directions","http://www.HOME-BUSINESS-ZONE.COM/Directions","http://www.IPIXHAWAII.COM/Directions","http://www.ISLANDPACIFICMORTGAGE.COM/Directions","http://www.JAIANANDA.COM/Directions","http://www.JOY-ORTIZ-ZIMMER.COM/Directions","http://www.KAIWACONSTRUCTION.COM/Directions","http://www.KAUAICONSTRUCTIONSERVICES.COM/Directions","http://www.KAUAIFOREIGNCARS.COM/Directions","http://www.KCSUSA.COM/Directions","http://www.KEALANANI.COM/Directions","http://www.KEAWECONCRETEPRODUCTS.COM/Directions","http://www.KEVINCROSSE.COM/Directions","http://www.KFOREIGNCARS.COM/Directions","http://www.KILAUEALIGHTHOUSEVILLAGE.COM/Directions","http://www.KINGAUTOCENTER.COM/Directions","http://www.KINGWINDWARD.COM/Directions","http://www.KUAI720AM.COM/Directions","http://www.KUKUI.COM/Directions","http://www.KUKUIIT.COM/Directions","http://www.KYMRY.COM/Directions","http://www.KYMRYPEREZ.COM/Directions","http://www.LETSGOHAWAII.COM/Directions","http://www.LISTSAFELY.COM/Directions","http://www.MACARTHURANDCOMPANY.COM/Directions","http://www.MACARTHURHAWAII.COM/Directions","http://www.MACARTHURHAWAIISIR.COM/Directions","http://www.MAUIREALE.COM/Directions","http://www.NAILERSTOOLBELTS.COM/Directions","http://www.NIKLBAG.COM/Directions","http://www.OBERGFREE.COM/Directions","http://www.OMEGA3-FISH-OIL.COM/Directions","http://www.OMEGA3FISHOIL.NET/Directions","http://www.ONARABAND.COM/Directions","http://www.PACIFICMOTORSERVICE.COM/Directions","http://www.PATTYCHANEY.COM/Directions","http://www.PENNYSBOOK.COM/Directions","http://www.PKYNO.COM/Directions","http://www.PREMIERHAWAIIREALESTATE.COM/Directions","http://www.PREMIUM-CAT-FOOD.COM/Directions","http://www.PREMIUM-DOG-FOOD.COM/Directions","http://www.PUNINANIPOOLS.COM/Directions","http://www.RANKINGMONKEY.COM/Directions","http://www.RANKINGSMONKEY.COM/Directions","http://www.REAL-ESTATE-GUIDE-KAUAI.COM/Directions","http://www.RFERRARO.COM/Directions","http://www.RONRAMIESINC.COM/Directions","http://www.RUSSELLKYONO.COM/Directions","http://www.SAFARIAVIATIONINC.COM/Directions","http://www.SAFARIAVIATIONS.COM/Directions","http://www.SAFARIHELICOPTERS.COM/Directions","http://www.SANJOSESAUTOSERVICE.COM/Directions","http://www.SANJOSESAUTOSERVICES.COM/Directions","http://www.SARATOGASHELL.COM/Directions","http://www.SHAKA103.COM/Directions","http://www.SLEEPINGGIANT.COM/Directions","http://www.SOLVSIMVIA.COMORKEY/Directions","http://www.SURF959FM.COM/Directions","http://www.SUSANLMARSHALL.COM/Directions","http://www.THEULTIMATEFISHOIL.COM/Directions","http://www.THOMASMCINERNEY.COM/Directions","http://www.VITAMUNDO.COM/Directions","http://www.WHEELCHAIR-KAUAI.COM/Directions","http://www.WOOSTERLLC.COM/Directions","http://www.ZONE-PRODUCTS.COM/Directions","http://www.ZONEANDTONE.COM","http://www.erniesservicecenter.com/Directions","http://www.thebumperguy.com/Directions","http://www.specializedautos.com/Directions","http://www.bosimports.com/Directions","http://www.cmautoservice.com/Directions","http://www.kirbergmotors.com/Directions","http://www.the-cardoctor.com/Directions","http://www.pacificfab.net/Directions","http://www.nissimotors.com/Directions","http://www.preferred-automotive.com/Directions","http://kukui.com/Directions","http://www.americasservicestation.com/Directions","http://www.wabcc.com/Directions","http://www.bing.com/Directions","http://www.waysidegarage.com/Directions","http://missionmuffler.com/Directions","http://www.freeksgarage.com/Directions","http://www.deepreflectionsdetailers.com/Directions","http://www.hoguebros.com/Directions","http://www.eriesrepair.com/Directions","http://www.cardoctor.com/Directions","http://www.eurocartech.com/Directions","http://www.hollandcarcare.com/Directions","http://waterstarmotors.com/Directions","http://www.theautodoctor.net/Directions","http://www.thegaragesf.com/Directions","http://www.ronramiesinc.com/Directions","http://www.ambassadorautoservice.com/Directions","http://www.andersonautomotive.net/Directions","http://www.americasservicestationacworth.com/Directions","http://www.menloshell.com/Directions","http://www.pacificmotorservice.com/Directions","http://www.precisionautorepair.com/Directions","http://www.foxautocare.com/Directions","http://www.pacificcarcare.com/Directions","http://www.fostercityvalero.com/Directions","http://www.americasservicestationbentwater.com/Directions","http://www.alisoviejoautoservice.com/Directions","http://www.starautomotive.com/Directions","http://www.salinasautobody.com/Directions","http://www.expressionsfloraldesigns.com/Directions","http://saratogashell.com/Directions","http://www.missionmuffler.com/Directions","http://www.americasservicestationlawerenceville.com/Directions","http://www.saddlebacktransmissions.com/Directions","http://www.allmotiverepair.com/Directions","http://www.fremontautobodyshop.com/Directions","http://www.americasservicestationalpharetta.com/Directions","http://moranautocenter.com/Directions","http://www.advancedautocenter.net/Directions","http://www.nixontireandauto.com/Directions","http://www.kensautobodyandstriping.com/Directions","http://www.completeautoslo.com/Directions","http://www.toolesgarage.com/Directions","http://www.saratogashell.com/Directions","http://www.danapointautomotive.com/Directions","http://www.scottshermanautocare.com/Directions","http://www.courtesyautomotiveservicesinc.com/Directions","http://www.jcautoslo.com/Directions","http://www.americasservicestationhamiltonmill.com/Directions","http://www.americasservicestationwoodstock.com/Directions","http://www.americasservicestationmarietta.com/Directions","http://www.bcautocenter.com/Directions","http://www.sanjosesautoservice.com/Directions","http://gilroypreferredautomotive.com/Directions","http://www.waterstarmotors.com/Directions","http://www.pasoroblesautorepair.com/Directions","http://www.autoelectricandfuel.com/Directions","http://www.moranautocenter.com/Directions","http://cardoctor.com/Directions");
	}
}

$r= new KUKUI_listings();
$r->parseCommandLine();

