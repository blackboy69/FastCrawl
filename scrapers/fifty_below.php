<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class fifty_below extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

		//$this->maxRetries = 100;
		//$this->timeout = 15;
		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=6;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		UPDATE load_queue SET processing = 1 WHERE url IN (SELECT url FROM raw_data)		 
			 ");
		db::query("UPDATE raw_data set parsed = 0 where type='fifty_below' and parsed = 1   ");
		*/
		// cananda top 100 cities by population
		//		db::query("UPDATE raw_data set parsed = 0 ");
//	db::query("DELETE FROM load_queue where type='$type'");
//		

		$this->noProxy=true;
//		$this->proxy = "localhost:8888";


		//$this->proxies = db::oneCol("SELECT PROXY.HTTP_PROXIES.proxy_host from PROXY.HTTP_PROXIES where active=1 order by last_updated desc");
		//log::info("Loaded ".sizeof($this->proxies)." Proxies");

		//$this->switchProxy(null,true);
		//$this->proxy = "219.234.82.76:17183";
		

		$this->bing = new search_engine_bing();
		$this->google = new search_engine_google();
		$this->yahoo = new search_engine_yahoo();

		$urls = array();
		$urls[]="http://5startire.net/contactus.htm";
		$urls[]="http://a2zmedsupply.com/contactus.htm";
		$urls[]="http://acorntire.com/contactus.htm";
		$urls[]="http://advancedmobilitysystems.com/contactus.htm";
		$urls[]="http://advantagetireor.com/contactus.htm";
		$urls[]="http://affordabletireca.com/contactus.htm";
		$urls[]="http://alabamawholesaletirepros.com/contactus.htm";
		$urls[]="http://albantire.com/contactus.htm";
		$urls[]="http://aldapegoodyear.com/contactus.htm";
		$urls[]="http://allentireva.com/contactus.htm";
		$urls[]="http://allmedllc.com/contactus.htm";
		$urls[]="http://alltireandwheel.com/contactus.htm";
		$urls[]="http://alstirecentre.com/contactus.htm";
		$urls[]="http://arkansastirepros.com/contactus.htm";
		$urls[]="http://atlanticavetireandservice.com/contactus.htm";
		$urls[]="http://aynortire.com/contactus.htm";
		$urls[]="http://babcockautocare.com/contactus.htm";
		$urls[]="http://bakers-automotive.com/contactus.htm";
		$urls[]="http://bandetireandalignment.com/contactus.htm";
		$urls[]="http://bannertirepros.com/contactus.htm";
		$urls[]="http://battlegroundwrecker.com/contactus.htm";
		$urls[]="http://belmacsupply.com/contactus.htm";
		$urls[]="http://bestbuyautoandtires.com/contactus.htm";
		$urls[]="http://bestbuytirecenters.com/contactus.htm";
		$urls[]="http://bigttire.net/contactus.htm";
		$urls[]="http://bischoffsmedical.com/contactus.htm";
		$urls[]="http://bobdavishomeimpco.com/contactus.htm";
		$urls[]="http://bobswheelalignment.net/contactus.htm";
		$urls[]="http://bonnettenterprises.com/contactus.htm";
		$urls[]="http://brucestire.com/contactus.htm";
		$urls[]="http://carlsonadvisorsllc.com/contactus.htm";
		$urls[]="http://ccrosenandsons.com/contactus.htm";
		$urls[]="http://classicpoolsandspas.com/contactus.htm";
		$urls[]="http://clevehillauto.com/contactus.htm";
		$urls[]="http://coloradotirepueblo.com/contactus.htm";
		$urls[]="http://commercialtireservices.com/contactus.htm";
		$urls[]="http://communitysurgical.com/contactus.htm";
		$urls[]="http://deltatirequincy.com/contactus.htm";
		$urls[]="http://desmoinestires.com/contactus.htm";
		$urls[]="http://dipietrotirecenter.com/contactus.htm";
		$urls[]="http://discountwheelntire.net/contactus.htm";
		$urls[]="http://dixonsautomotive.com/contactus.htm";
		$urls[]="http://dmmedical.net/contactus.htm";
		$urls[]="http://drakestire.com/contactus.htm";
		$urls[]="http://dumastirepros.com/contactus.htm";
		$urls[]="http://expertechautorepair.com/contactus.htm";
		$urls[]="http://familytireandautoservice.com/contactus.htm";
		$urls[]="http://faribaulttire.com/contactus.htm";
		$urls[]="http://fisktireandauto.com/contactus.htm";
		$urls[]="http://fredandwaynes.com/contactus.htm";
		$urls[]="http://freesaltsystem.com/contactus.htm";
		$urls[]="http://freestateauto.com/contactus.htm";
		$urls[]="http://fsteddie.com/contactus.htm";
		$urls[]="http://garciatire.com/contactus.htm";
		$urls[]="http://geraldstires.com/contactus.htm";
		$urls[]="http://go2thepitstop.com/contactus.htm";
		$urls[]="http://greatrivertire.com/contactus.htm";
		$urls[]="http://griffintire.com/contactus.htm";
		$urls[]="http://gronemansshamrock.com/contactus.htm";
		$urls[]="http://grussendorfnursery.com/contactus.htm";
		$urls[]="http://harrisonoil.com/contactus.htm";
		$urls[]="http://helpingwithhorsepower.com/contactus.htm";
		$urls[]="http://hermannmedical.com/contactus.htm";
		$urls[]="http://hesstireservice.com/contactus.htm";
		$urls[]="http://hilltires.com/contactus.htm";
		$urls[]="http://hjtires.com/contactus.htm";
		$urls[]="http://hubcapandwheel.net/contactus.htm";
		$urls[]="http://hudsontireexchange.com/contactus.htm";
		$urls[]="http://hummeltire.com/contactus.htm";
		$urls[]="http://importspecialistofindy.com/contactus.htm";
		$urls[]="http://integritycarcare.com/contactus.htm";
		$urls[]="http://integritypoolrepair.com/contactus.htm";
		$urls[]="http://itcmedical.com/contactus.htm";
		$urls[]="http://jacksoktire.com/contactus.htm";
		$urls[]="http://jaimestirestore.com/contactus.htm";
		$urls[]="http://jamesmedical.com/contactus.htm";
		$urls[]="http://jamiestire.com/contactus.htm";
		$urls[]="http://jce.com/contactus.htm";
		$urls[]="http://jerrysshell.com/contactus.htm";
		$urls[]="http://jessemartinezautocenter.com/contactus.htm";
		$urls[]="http://johnsontireservice.com/contactus.htm";
		$urls[]="http://jonestirewholesale.com/contactus.htm";
		$urls[]="http://kennedysbannertire.com/contactus.htm";
		$urls[]="http://klingemanncarcare.com/contactus.htm";
		$urls[]="http://kosttire.com/contactus.htm";
		$urls[]="http://lakemurraytireandauto.com/contactus.htm";
		$urls[]="http://larrysnewandusedtiresinc.com/contactus.htm";
		$urls[]="http://lestersautomotivecenter.com/contactus.htm";
		$urls[]="http://libertytireinc.net/contactus.htm";
		$urls[]="http://losamigostirewheel.com/contactus.htm";
		$urls[]="http://lucastires.com/contactus.htm";
		$urls[]="http://maderstransmission.com/contactus.htm";
		$urls[]="http://mannixpools.net/contactus.htm";
		$urls[]="http://megawheelz.com/contactus.htm";
		$urls[]="http://metricconcrete.com/contactus.htm";
		$urls[]="http://metrocarewi.com/contactus.htm";
		$urls[]="http://metroindustrialtire.com/contactus.htm";
		$urls[]="http://mnwholesaletireandwheel.com/contactus.htm";
		$urls[]="http://monitormedical.com/contactus.htm";
		$urls[]="http://moorepatron.com/contactus.htm";
		$urls[]="http://moorerobinson.com/contactus.htm";
		$urls[]="http://motorcarstirecenter.com/contactus.htm";
		$urls[]="http://newenglandsurg.com/contactus.htm";
		$urls[]="http://newenglandthermoking.com/contactus.htm";
		$urls[]="http://newtireprice.com/contactus.htm";
		$urls[]="http://northeastmstirepros.com/contactus.htm";
		$urls[]="http://nwtire.com/contactus.htm";
		$urls[]="http://palmbeachtire.com/contactus.htm";
		$urls[]="http://palmerpoolsales.com/contactus.htm";
		$urls[]="http://paradisespasandmotorsports.com/contactus.htm";
		$urls[]="http://pattontireandauto.com/contactus.htm";
		$urls[]="http://paulstireandfurniture.com/contactus.htm";
		$urls[]="http://peasleeservicecenter.com/contactus.htm";
		$urls[]="http://peninsulatotalcarcare.com/contactus.htm";
		$urls[]="http://perrybros.com/contactus.htm";
		$urls[]="http://pharmasavemonroe.com/contactus.htm";
		$urls[]="http://platinumtireandwheel.com/contactus.htm";
		$urls[]="http://portablestoragebytkm.com/contactus.htm";
		$urls[]="http://porterfieldtireinc.com/contactus.htm";
		$urls[]="http://radialtirecompany.com/contactus.htm";
		$urls[]="http://redhorsetire.com/contactus.htm";
		$urls[]="http://rhdtire.net/contactus.htm";
		$urls[]="http://ricefarmersco-op.com/contactus.htm";
		$urls[]="http://riveroakspaintandbody.net/contactus.htm";
		$urls[]="http://rochesterautocare.com/contactus.htm";
		$urls[]="http://rockystirepros.com/contactus.htm";
		$urls[]="http://roystires.com/contactus.htm";
		$urls[]="http://s1427.endeavorsuite.biz/contactus.htm";
		$urls[]="http://s46.endeavorsuite.biz/contactus.htm";
		$urls[]="http://sabatinoschicagosausage.com/contactus.htm";
		$urls[]="http://salemtire.com/contactus.htm";
		$urls[]="http://salinastiresonline.com/contactus.htm";
		$urls[]="http://sevierfarmerscoop.com/contactus.htm";
		$urls[]="http://shenvalleytires.com/contactus.htm";
		$urls[]="http://simivalleytirepros.com/contactus.htm";
		$urls[]="http://sinnottblacktop.com/contactus.htm";
		$urls[]="http://southernrubbertirepros.com/contactus.htm";
		$urls[]="http://statetire.org/contactus.htm";
		$urls[]="http://steeleguiltnertirepros.com/contactus.htm";
		$urls[]="http://stvincentshms.com/contactus.htm";
		$urls[]="http://superbuytiresandwheels.net/contactus.htm";
		$urls[]="http://superiorchoice.com/contactus.htm";
		$urls[]="http://superioroilexpresstx.com/contactus.htm";
		$urls[]="http://tetraulttirepros.com/contactus.htm";
		$urls[]="http://thermokinggreenbay.com/contactus.htm";
		$urls[]="http://tireengineers.com/contactus.htm";
		$urls[]="http://tireexchange.com/contactus.htm";
		$urls[]="http://tirevillage.com/contactus.htm";
		$urls[]="http://tkofhouston.net/contactus.htm";
		$urls[]="http://torn2shredz.com/contactus.htm";
		$urls[]="http://tristatepools.com/contactus.htm";
		$urls[]="http://triwtirecenter.com/contactus.htm";
		$urls[]="http://ultimate-carcare.com/contactus.htm";
		$urls[]="http://universitytireandauto.com/contactus.htm";
		$urls[]="http://upstatetirepros.com/contactus.htm";
		$urls[]="http://virginiaautomotiveservice.com/contactus.htm";
		$urls[]="http://westerntireauto.com/contactus.htm";
		$urls[]="http://williamsbraketuneandtire.net/contactus.htm";
		$urls[]="http://wiwholesaletire.com/contactus.htm";
		$urls[]="http://www.17thstreetautomotive.com/contactus.htm";
		$urls[]="http://www.602autosports.com/contactus.htm";
		$urls[]="http://www.8thandlabreafirestone.com/contactus.htm";
		$urls[]="http://www.aahayestireandbattery.com/contactus.htm";
		$urls[]="http://www.aattc.com/contactus.htm";
		$urls[]="http://www.acorntire.com/contactus.htm";
		$urls[]="http://www.acttireandautomotive.net/contactus.htm";
		$urls[]="http://www.advancedmobilitysystems.com/contactus.htm";
		$urls[]="http://www.advantagetireor.com/contactus.htm";
		$urls[]="http://www.afctx.com/contactus.htm";
		$urls[]="http://www.affordabletireca.com/contactus.htm";
		$urls[]="http://www.airportroadautocenter.com/contactus.htm";
		$urls[]="http://www.albantire.com/contactus.htm";
		$urls[]="http://www.aldapegoodyear.com/contactus.htm";
		$urls[]="http://www.allabouttireandbrake.com/contactus.htm";
		$urls[]="http://www.allstarservicecenter.com/contactus.htm";
		$urls[]="http://www.alltireandbrake.com/contactus.htm";
		$urls[]="http://www.alltireandwheel.net/contactus.htm";
		$urls[]="http://www.americantire.net/contactus.htm";
		$urls[]="http://www.americantiredepotspokane.com/contactus.htm";
		$urls[]="http://www.anglodutchpoolsandtoys.com/contactus.htm";
		$urls[]="http://www.apostolicsabetha.com/contactus.htm";
		$urls[]="http://www.aquaproswimmingpools.com/contactus.htm";
		$urls[]="http://www.aquatimepools.com/contactus.htm";
		$urls[]="http://www.autoaccessoriesunlimited.net/contactus.htm";
		$urls[]="http://www.automotiveoutfitters.com/contactus.htm";
		$urls[]="http://www.autosmartkansas.com/contactus.htm";
		$urls[]="http://www.autotirespecialists.com/contactus.htm";
		$urls[]="http://www.aynortire.com/contactus.htm";
		$urls[]="http://www.baileyspools.com/contactus.htm";
		$urls[]="http://www.baitysdiscounttiresales.com/contactus.htm";
		$urls[]="http://www.baitytireandservice.com/contactus.htm";
		$urls[]="http://www.bargainbarntire.com/contactus.htm";
		$urls[]="http://www.bargainbarntirecenter.com/contactus.htm";
		$urls[]="http://www.baucomservices.net/contactus.htm";
		$urls[]="http://www.baumgarttireandwheel.com/contactus.htm";
		$urls[]="http://www.bergeystire.com/contactus.htm";
		$urls[]="http://www.bergeystires.com/contactus.htm";
		$urls[]="http://www.bergeystrucktire.com/contactus.htm";
		$urls[]="http://www.bighorntire.com/contactus.htm";
		$urls[]="http://www.bigjohnsperformance.com/contactus.htm";
		$urls[]="http://www.bjamericancarcare.com/contactus.htm";
		$urls[]="http://www.bluemountaintirepros.com/contactus.htm";
		$urls[]="http://www.bobstireservice.com/contactus.htm";
		$urls[]="http://www.bobswheelalignment.net/contactus.htm";
		$urls[]="http://www.bryansroadtire.com/contactus.htm";
		$urls[]="http://www.buffalotirerichmond.com/contactus.htm";
		$urls[]="http://www.burlingtontires.com/contactus.htm";
		$urls[]="http://www.burnettpoolsandspas.com/contactus.htm";
		$urls[]="http://www.butlertireinc.com/contactus.htm";
		$urls[]="http://www.butlertiresouthside.com/contactus.htm";
		$urls[]="http://www.calmedpharmacy.com/contactus.htm";
		$urls[]="http://www.carlkingtire.com/contactus.htm";
		$urls[]="http://www.carmichaeltire.com/contactus.htm";
		$urls[]="http://www.casteelstire.com/contactus.htm";
		$urls[]="http://www.chairequip.net/contactus.htm";
		$urls[]="http://www.chapmanwilson.com/contactus.htm";
		$urls[]="http://www.charlestontireandauto.com/contactus.htm";
		$urls[]="http://www.chelseatireandservice.com/contactus.htm";
		$urls[]="http://www.cheneytire.com/contactus.htm";
		$urls[]="http://www.cherryvalleyspas.com/contactus.htm";
		$urls[]="http://www.chichestershomecare.net/contactus.htm";
		$urls[]="http://www.christianbroroofing.com/contactus.htm";
		$urls[]="http://www.cktireservice.com/contactus.htm";
		$urls[]="http://www.claydooley.com/contactus.htm";
		$urls[]="http://www.cloverleaftireandauto.com/contactus.htm";
		$urls[]="http://www.cmhhomehealthcare.com/contactus.htm";
		$urls[]="http://www.columbiatireandauto.com/contactus.htm";
		$urls[]="http://www.columbustireandservice.com/contactus.htm";
		$urls[]="http://www.combsautoservice.com/contactus.htm";
		$urls[]="http://www.commercialtireservices.com/contactus.htm";
		$urls[]="http://www.communitysurgical.com/contactus.htm";
		$urls[]="http://www.corsitire.net/contactus.htm";
		$urls[]="http://www.cottagesurgicalsupply.com/contactus.htm";
		$urls[]="http://www.countylinecarcare.com/contactus.htm";
		$urls[]="http://www.cozy-cover.com/contactus.htm";
		$urls[]="http://www.creativepools.org/contactus.htm";
		$urls[]="http://www.customcarcareva.com/contactus.htm";
		$urls[]="http://www.customexhaustshop.com/contactus.htm";
		$urls[]="http://www.dahlrecruiting.com/contactus.htm";
		$urls[]="http://www.dakotatirebrakesandmore.com/contactus.htm";
		$urls[]="http://www.dakotatirefargo.com/contactus.htm";
		$urls[]="http://www.dallaswheelchairs.com/contactus.htm";
		$urls[]="http://www.dellingerstire.com/contactus.htm";
		$urls[]="http://www.deltatire.net/contactus.htm";
		$urls[]="http://www.dennysservicecenter.com/contactus.htm";
		$urls[]="http://www.dettmertires.com/contactus.htm";
		$urls[]="http://www.diamondtirecenter.com/contactus.htm";
		$urls[]="http://www.dieztirecompany.com/contactus.htm";
		$urls[]="http://www.dinosaurtirepros.com/contactus.htm";
		$urls[]="http://www.discounttireandbrake.com/contactus.htm";
		$urls[]="http://www.discounttireservice.com/contactus.htm";
		$urls[]="http://www.dixietire.com/contactus.htm";
		$urls[]="http://www.duboiscountytire.com/contactus.htm";
		$urls[]="http://www.duettstire.com/contactus.htm";
		$urls[]="http://www.eagletire.com/contactus.htm";
		$urls[]="http://www.eastendmotor.com/contactus.htm";
		$urls[]="http://www.eaverstirepros.com/contactus.htm";
		$urls[]="http://www.elamigotire.net/contactus.htm";
		$urls[]="http://www.elstireservice.com/contactus.htm";
		$urls[]="http://www.erniessouthernoffroad.com/contactus.htm";
		$urls[]="http://www.estestire.com/contactus.htm";
		$urls[]="http://www.etddiscounttire.com/contactus.htm";
		$urls[]="http://www.etires4less.com/contactus.htm";
		$urls[]="http://www.expresstiretx.com/contactus.htm";
		$urls[]="http://www.fairfieldtireandauto.com/contactus.htm";
		$urls[]="http://www.familytireandautoservice.com/contactus.htm";
		$urls[]="http://www.farrellshomehealth.com/contactus.htm";
		$urls[]="http://www.fattboyzdayton.com/contactus.htm";
		$urls[]="http://www.federaltireinc.com/contactus.htm";
		$urls[]="http://www.feverriveroutfitters.com/contactus.htm";
		$urls[]="http://www.fiberclass.net/contactus.htm";
		$urls[]="http://www.fisktireandauto.com/contactus.htm";
		$urls[]="http://www.fletcherstireandauto.net/contactus.htm";
		$urls[]="http://www.fountainvalleytireandauto.com/contactus.htm";
		$urls[]="http://www.fourseasonstire.com/contactus.htm";
		$urls[]="http://www.franklinautoinc.com/contactus.htm";
		$urls[]="http://www.fredericksiga.com/contactus.htm";
		$urls[]="http://www.fredstires.net/contactus.htm";
		$urls[]="http://www.freemansservicecentre.com/contactus.htm";
		$urls[]="http://www.freestateauto.com/contactus.htm";
		$urls[]="http://www.freewaytire.biz/contactus.htm";
		$urls[]="http://www.fremonttire.com/contactus.htm";
		$urls[]="http://www.froeseltire.com/contactus.htm";
		$urls[]="http://www.fullerstire.com/contactus.htm";
		$urls[]="http://www.gallaghertires.com/contactus.htm";
		$urls[]="http://www.gandjauto.com/contactus.htm";
		$urls[]="http://www.genesisoxygen.com/contactus.htm";
		$urls[]="http://www.georgeorentires.com/contactus.htm";
		$urls[]="http://www.gilbertstirepros.com/contactus.htm";
		$urls[]="http://www.glennstires.com/contactus.htm";
		$urls[]="http://www.goldcoasttire.com/contactus.htm";
		$urls[]="http://www.goldencircletirepros.com/contactus.htm";
		$urls[]="http://www.gomeztiresinc.com/contactus.htm";
		$urls[]="http://www.gradystireandauto.com/contactus.htm";
		$urls[]="http://www.grahamtire.net/contactus.htm";
		$urls[]="http://www.greatrivertire.com/contactus.htm";
		$urls[]="http://www.greatwesterntire.com/contactus.htm";
		$urls[]="http://www.griffintireandauto.com/contactus.htm";
		$urls[]="http://www.hallsservicecenter.com/contactus.htm";
		$urls[]="http://www.halltire.com/contactus.htm";
		$urls[]="http://www.haroldsfarmsupply.com/contactus.htm";
		$urls[]="http://www.healthcareaccessories.com/contactus.htm";
		$urls[]="http://www.healthcareplus.org/contactus.htm";
		$urls[]="http://www.healthcarerxequip.com/contactus.htm";
		$urls[]="http://www.helpingwithhorsepower.com/contactus.htm";
		$urls[]="http://www.highlandtire.com/contactus.htm";
		$urls[]="http://www.hillsboroamericantire.com/contactus.htm";
		$urls[]="http://www.hmespecialists.net/contactus.htm";
		$urls[]="http://www.hollywoodmedical.com/contactus.htm";
		$urls[]="http://www.homesteadtireandauto.com/contactus.htm";
		$urls[]="http://www.hometowntireauto.com/contactus.htm";
		$urls[]="http://www.htmotors.com/contactus.htm";
		$urls[]="http://www.hughestire.com/contactus.htm";
		$urls[]="http://www.illinoisautothermoking.com/contactus.htm";
		$urls[]="http://www.integritypoolrepair.com/contactus.htm";
		$urls[]="http://www.islandautorepairca.com/contactus.htm";
		$urls[]="http://www.jackstireservice.com/contactus.htm";
		$urls[]="http://www.jce.com/contactus.htm";
		$urls[]="http://www.jeffbarnesauto.com/contactus.htm";
		$urls[]="http://www.jeffsurgical.com/contactus.htm";
		$urls[]="http://www.jensentire.ca/contactus.htm";
		$urls[]="http://www.jessemartinezautocenter.com/contactus.htm";
		$urls[]="http://www.jimgrizzletire.com/contactus.htm";
		$urls[]="http://www.jimshomehealth.com/contactus.htm";
		$urls[]="http://www.jimstire.com/contactus.htm";
		$urls[]="http://www.jimstirecenter.com/contactus.htm";
		$urls[]="http://www.jimwellstirecenter.com/contactus.htm";
		$urls[]="http://www.jimwhiteheadtire.com/contactus.htm";
		$urls[]="http://www.johnsontireservice.com/contactus.htm";
		$urls[]="http://www.johnsontireservice.net/contactus.htm";
		$urls[]="http://www.kansastire.com/contactus.htm";
		$urls[]="http://www.karkarewestcolumbia.com/contactus.htm";
		$urls[]="http://www.kellettskorner.com/contactus.htm";
		$urls[]="http://www.kenconnersservicetire.com/contactus.htm";
		$urls[]="http://www.kendziatire.com/contactus.htm";
		$urls[]="http://www.kennyknoxtire.com/contactus.htm";
		$urls[]="http://www.kennyspeeds.com/contactus.htm";
		$urls[]="http://www.kenstires.com/contactus.htm";
		$urls[]="http://www.kentperformanceauto.com/contactus.htm";
		$urls[]="http://www.keystonetire.net/contactus.htm";
		$urls[]="http://www.kingwoodtireandauto.com/contactus.htm";
		$urls[]="http://www.kirkwoodautocenter.com/contactus.htm";
		$urls[]="http://www.kpsprincetongarage.com/contactus.htm";
		$urls[]="http://www.lewiselectronics.com/contactus.htm";
		$urls[]="http://www.libertytireco.com/contactus.htm";
		$urls[]="http://www.littletire.com/contactus.htm";
		$urls[]="http://www.lmrtires.com/contactus.htm";
		$urls[]="http://www.lylestire.com/contactus.htm";
		$urls[]="http://www.mackalgertire.com/contactus.htm";
		$urls[]="http://www.madilldance.com/contactus.htm";
		$urls[]="http://www.majesticpoolsandspas.com/contactus.htm";
		$urls[]="http://www.manleytire.com/contactus.htm";
		$urls[]="http://www.marestire.com/contactus.htm";
		$urls[]="http://www.markdrugmedicalsupply.com/contactus.htm";
		$urls[]="http://www.marshallmobilityplus.com/contactus.htm";
		$urls[]="http://www.marshallspasandpools.com/contactus.htm";
		$urls[]="http://www.masonmedical.com/contactus.htm";
		$urls[]="http://www.matthewstirecenter.net/contactus.htm";
		$urls[]="http://www.maurertire.com/contactus.htm";
		$urls[]="http://www.maverickpools.com/contactus.htm";
		$urls[]="http://www.mccoystire.com/contactus.htm";
		$urls[]="http://www.mcgrifftire.com/contactus.htm";
		$urls[]="http://www.mctrongolfcarts.com/contactus.htm";
		$urls[]="http://www.med-south.com/contactus.htm";
		$urls[]="http://www.medsolutionsllc.com/contactus.htm";
		$urls[]="http://www.megawheelz.com/contactus.htm";
		$urls[]="http://www.midtowneiga.com/contactus.htm";
		$urls[]="http://www.mightymuffler.net/contactus.htm";
		$urls[]="http://www.mikesfairwoodauto.net/contactus.htm";
		$urls[]="http://www.mikesmithtirepros.com/contactus.htm";
		$urls[]="http://www.mmexpresssales.com/contactus.htm";
		$urls[]="http://www.mobilitymedicalinc.com/contactus.htm";
		$urls[]="http://www.moorethantires.com/contactus.htm";
		$urls[]="http://www.mooretires.com/contactus.htm";
		$urls[]="http://www.morethantires.com/contactus.htm";
		$urls[]="http://www.morrisontire.com/contactus.htm";
		$urls[]="http://www.motorcarstirecenter.com/contactus.htm";
		$urls[]="http://www.mrnobodys.com/contactus.htm";
		$urls[]="http://www.namura.com/contactus.htm";
		$urls[]="http://www.newtireprice.com/contactus.htm";
		$urls[]="http://www.nextire.com/contactus.htm";
		$urls[]="http://www.nichelsontire.com/contactus.htm";
		$urls[]="http://www.nolanddrilling.com/contactus.htm";
		$urls[]="http://www.northernpharmacy.com/contactus.htm";
		$urls[]="http://www.northmissouritire.com/contactus.htm";
		$urls[]="http://www.northsidedc.com/contactus.htm";
		$urls[]="http://www.nuttalltire.com/contactus.htm";
		$urls[]="http://www.obrianhealthcare.com/contactus.htm";
		$urls[]="http://www.oceanairtirepros.com/contactus.htm";
		$urls[]="http://www.olinmott.com/contactus.htm";
		$urls[]="http://www.onsiteduluth.com/contactus.htm";
		$urls[]="http://www.ontariomillstirepros.com/contactus.htm";
		$urls[]="http://www.orangevaletirepros.com/contactus.htm";
		$urls[]="http://www.otdra.com/contactus.htm";
		$urls[]="http://www.oxforda1tire.com/contactus.htm";
		$urls[]="http://www.palmerpoolsales.com/contactus.htm";
		$urls[]="http://www.paradocksgrille.com/contactus.htm";
		$urls[]="http://www.parkertireandalignment.com/contactus.htm";
		$urls[]="http://www.pasmonroe.com/contactus.htm";
		$urls[]="http://www.pattontireandauto.com/contactus.htm";
		$urls[]="http://www.paulwilliamstire.com/contactus.htm";
		$urls[]="http://www.peninsulatotalcarcare.com/contactus.htm";
		$urls[]="http://www.perkinsmedicalsupply.net/contactus.htm";
		$urls[]="http://www.petesmithauto.com/contactus.htm";
		$urls[]="http://www.picomedical.com/contactus.htm";
		$urls[]="http://www.piedmonttireandauto.com/contactus.htm";
		$urls[]="http://www.piedmonttirepros.com/contactus.htm";
		$urls[]="http://www.plus1tirepros.com/contactus.htm";
		$urls[]="http://www.plymouthtireandauto.com/contactus.htm";
		$urls[]="http://www.poinsetttires.com/contactus.htm";
		$urls[]="http://www.ponderosatirepros.com/contactus.htm";
		$urls[]="http://www.poolworksinc.com/contactus.htm";
		$urls[]="http://www.premiumtireandauto.com/contactus.htm";
		$urls[]="http://www.protireduluth.com/contactus.htm";
		$urls[]="http://www.prowheelsales.com/contactus.htm";
		$urls[]="http://www.prowheelsandtires.com/contactus.htm";
		$urls[]="http://www.qualitytireoflansing.com/contactus.htm";
		$urls[]="http://www.qualitytires.ca/contactus.htm";
		$urls[]="http://www.qualitytrucktires.com/contactus.htm";
		$urls[]="http://www.quietzoneautorepair.com/contactus.htm";
		$urls[]="http://www.radialtirecompany.com/contactus.htm";
		$urls[]="http://www.radperformance.com/contactus.htm";
		$urls[]="http://www.ralphstirepros.com/contactus.htm";
		$urls[]="http://www.ramonatire.com/contactus.htm";
		$urls[]="http://www.ranchotireslv.net/contactus.htm";
		$urls[]="http://www.randystire.com/contactus.htm";
		$urls[]="http://www.redburntireco.com/contactus.htm";
		$urls[]="http://www.reliabletirecare.com/contactus.htm";
		$urls[]="http://www.ricksspa.com/contactus.htm";
		$urls[]="http://www.ridgeviewcountryclub.com/contactus.htm";
		$urls[]="http://www.rileyparktire.com/contactus.htm";
		$urls[]="http://www.rimandtirepro.ca/contactus.htm";
		$urls[]="http://www.riostires.com/contactus.htm";
		$urls[]="http://www.riverdaletirecenter.com/contactus.htm";
		$urls[]="http://www.riveroakspaintandbody.net/contactus.htm";
		$urls[]="http://www.rochesterautocare.com/contactus.htm";
		$urls[]="http://www.rolandstire.com/contactus.htm";
		$urls[]="http://www.roystires.com/contactus.htm";
		$urls[]="http://www.rubbertracksolutions.com/contactus.htm";
		$urls[]="http://www.samthehubcapman.com/contactus.htm";
		$urls[]="http://www.shoptireworld.com/contactus.htm";
		$urls[]="http://www.silvercitytire.net/contactus.htm";
		$urls[]="http://www.silverlakeauto.com/contactus.htm";
		$urls[]="http://www.siouxfallsnapa.com/contactus.htm";
		$urls[]="http://www.skipsauto.com/contactus.htm";
		$urls[]="http://www.smithandsontire.com/contactus.htm";
		$urls[]="http://www.southtownfirestone.com/contactus.htm";
		$urls[]="http://www.sparklingpoolsmiami.com/contactus.htm";
		$urls[]="http://www.statetire.org/contactus.htm";
		$urls[]="http://www.steeleguiltnertirepros.com/contactus.htm";
		$urls[]="http://www.steepletontire.com/contactus.htm";
		$urls[]="http://www.stmtires.com/contactus.htm";
		$urls[]="http://www.stuartbauerpools.com/contactus.htm";
		$urls[]="http://www.suburbantire.com/contactus.htm";
		$urls[]="http://www.sullivanspharmacy.com/contactus.htm";
		$urls[]="http://www.summerfieldautoservice.com/contactus.htm";
		$urls[]="http://www.sunpoolstx.com/contactus.htm";
		$urls[]="http://www.sunshineservicealignment.com/contactus.htm";
		$urls[]="http://www.superdealtireservice.com/contactus.htm";
		$urls[]="http://www.superiortireco.com/contactus.htm";
		$urls[]="http://www.tannerservicecenter.com/contactus.htm";
		$urls[]="http://www.tdstires.net/contactus.htm";
		$urls[]="http://www.terwilligerstirepros.com/contactus.htm";
		$urls[]="http://www.tetraulttirepros.com/contactus.htm";
		$urls[]="http://www.texastire-amarillo.com/contactus.htm";
		$urls[]="http://www.theautoexperts.net/contactus.htm";
		$urls[]="http://www.thermokinggreenbay.com/contactus.htm";
		$urls[]="http://www.thermokingomaha.com/contactus.htm";
		$urls[]="http://www.timstreads.com/contactus.htm";
		$urls[]="http://www.tirecenterplustirepros.com/contactus.htm";
		$urls[]="http://www.tirecentral.net/contactus.htm";
		$urls[]="http://www.tirecountry.net/contactus.htm";
		$urls[]="http://www.tireempireautocenter.com/contactus.htm";
		$urls[]="http://www.tirefactorystore.com/contactus.htm";
		$urls[]="http://www.tireoneautocenter.com/contactus.htm";
		$urls[]="http://www.tires4denver.com/contactus.htm";
		$urls[]="http://www.tiresalesandserviceinc.com/contactus.htm";
		$urls[]="http://www.tiresplusdetroitlakes.com/contactus.htm";
		$urls[]="http://www.tiresplusnd.com/contactus.htm";
		$urls[]="http://www.tiretracksusa.com/contactus.htm";
		$urls[]="http://www.tireworksinc.com/contactus.htm";
		$urls[]="http://www.tkwinnipeg.com/contactus.htm";
		$urls[]="http://www.tpstire.com/contactus.htm";
		$urls[]="http://www.trailtire.com/contactus.htm";
		$urls[]="http://www.trammelltirecompany.com/contactus.htm";
		$urls[]="http://www.trippstire.com/contactus.htm";
		$urls[]="http://www.tristatestairlifts.com/contactus.htm";
		$urls[]="http://www.tristatetireandauto.com/contactus.htm";
		$urls[]="http://www.trltireservice.com/contactus.htm";
		$urls[]="http://www.tropicalpoolsinc.net/contactus.htm";
		$urls[]="http://www.trucktiresamerica.com/contactus.htm";
		$urls[]="http://www.tubbieshomefurnishings.com/contactus.htm";
		$urls[]="http://www.twinvalleytire.com/contactus.htm";
		$urls[]="http://www.uniqueautotrenz.com/contactus.htm";
		$urls[]="http://www.usaexpresstire.com/contactus.htm";
		$urls[]="http://www.valleyautohaus.com/contactus.htm";
		$urls[]="http://www.victoriapoolservice.net/contactus.htm";
		$urls[]="http://www.victorydrug.com/contactus.htm";
		$urls[]="http://www.virginiaautomotiveservice.com/contactus.htm";
		$urls[]="http://www.walkertire.com/contactus.htm";
		$urls[]="http://www.warnertpromotions.com/contactus.htm";
		$urls[]="http://www.wastlerautoservice.com/contactus.htm";
		$urls[]="http://www.waukegantire.net/contactus.htm";
		$urls[]="http://www.waynehealthservices.com/contactus.htm";
		$urls[]="http://www.wbhcp.com/contactus.htm";
		$urls[]="http://www.webertires.net/contactus.htm";
		$urls[]="http://www.weirtontire.com/contactus.htm";
		$urls[]="http://www.wellingtontire.com/contactus.htm";
		$urls[]="http://www.westerntireauto.com/contactus.htm";
		$urls[]="http://www.westsidetire.net/contactus.htm";
		$urls[]="http://www.wheeldepotca.com/contactus.htm";
		$urls[]="http://www.wholesaletirewarren.com/contactus.htm";
		$urls[]="http://www.wilburjamestirepros.com/contactus.htm";
		$urls[]="http://www.wimmertireandbrake.com/contactus.htm";
		$urls[]="http://www.woodsservicecenter.com/contactus.htm";
		$urls[]="http://www.wrighttire.net/contactus.htm";
		$urls[]="http://www.wwtireservice.com/contactus.htm";
		$urls[]="http://www.yatesautomotiveva.com/contactus.htm";
		$urls[]="http://www.youngstire.net/contactus.htm";
		$urls[]="http://www.yournexttirestore.com/contactus.htm";
		$urls[]="http://www.zissertire.com/contactus.htm";
		$urls[]="http://zimstires.com/contactus.htm";

		for($i=1;$i<150;$i++)
		{
			$urls[] = $this->yahoo->url('"Powered by 50 Below, an ARI Company"',$i);
		}
		$this->loadUrlsByArray($urls);
	}

	static function parse($url,$html)
	{
		$thiz = self::getInstance();
//		$this->yahoo = new search_engine_yahoo();
		$type = get_class();		
		$yahooUrls = $thiz->yahoo->parse($html,false);
		$toLoad = array();

		foreach($yahooUrls as $yahooUrl)
		{
			if (preg_match("/yahoo|bing/i", $url))
			{
				$host = parse_url($yahooUrl,PHP_URL_HOST);
				$toLoad[] = "http://$host/contactus.htm";
			}
			else
			{
				$toLoad[] = $yahooUrl;
			}
		}
		print_R($toLoad);
		$thiz->loadUrlsByArray($toLoad);

		$type = get_class();		
		$thiz = self::getInstance();
		$x = new  XPath($html);	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$urls = array();
		
		parse_url($url,PHP_URL_HOST);
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		

		$data = array();
		foreach($x->query("//div[@class='cms_contactInformationItemTitle']") as $node)
		{
			$data['COMPANY'] = self::cleanup($node->textContent);
		}

		if (isset($data['COMPANY']))
		{
			$line = array();
			foreach($x->query("//div[@class='cms_contactInformationItemline']") as $node)
			{
				$line[] = self::cleanup($node->textContent);
			}
			$data = array_merge($data, $ap->parse(join(",", $line)));
			$data = array_merge($data, $ep->parse(join(",", $line)));
			$data = array_merge($data, $pp->parse(join(",", $line)));

			// Demandforce requirements
			$data['FIRST_NAME']="Not Provided";
			$data['LAST_NAME']="Not Provided";
			if(preg_match("/ |[a-z]/", @$data['ZIP']))
				$data['COUNTRY'] = 'Canada';
			else
				$data['COUNTRY'] = 'United States';

			$data['SOURCE_URL'] = $url;
			log::info($data);

			db::store($type,$data,array('SOURCE_URL'));	
		}
	}
}

$r= new fifty_below();
$r->parseCommandLine();

