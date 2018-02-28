<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";
/*

http://www.ip-address.org/reverse-lookup/reverse-ip.php
http://www.yougetsignal.com/tools/web-sites-on-web-server/

*/
class advisorwebsites extends baseScrape
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
		$this->threads=4;
		$this->debug=false;
		$this->maxRetries   = 0;
		$this->sleepInterval= 2; 
//		$this->retryEnabled=false;
//		db::query("DROP TABLE $type");	
	//	db::query("UPDATE raw_data set parsed = 0 where type='advisorwebsites' and url = 'www.gaetangoudreau.com/contact'");

db::query("UPDATE raw_data set parsed = 0 where type='advisorwebsites' and parsed = 1 ");
db::query("DROP TABLE $type");
#db::query("DELETE FROM raw_data  where type='advisorwebsites' and url like '%contact-us' ");
#db::query("DELETE FROM load_queue  where type='advisorwebsites' and url like '%contact-us' ");
		/*db::query("UPDATE raw_data set parsed = 0 where type='advisorwebsites' and parsed = 1 ");
		
	
		
		
		
		db::query("UPDATE load_queue set processing = 0 where type='advisorwebsites' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='advisorwebsites' and parsed = 1 ");	

	
		db::query("DELETE FROM load_queue where type='advisorwebsites' ");	
		db::query("DELETE FROM raw_data where type='advisorwebsites' ");
*/
		
		//$this->loadUrl($this->yahoo->url('"website by advisorwebsites"'));
		//$this->loadUrl($this->bing->url('"website by advisorwebsites"'));
//		$this->loadUrlsByArray(self::allTheLinks());
		$this->loadUrl("http://www.gaetangoudreau.com/contact/",true);
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		
		$ap = new Address_Parser();
		$op = new Operating_Hours_Parser();
		$pp = new Phone_Parser();
		$kvp = new KeyValue_Parser();
		$host = parse_url($url,PHP_URL_HOST);
		
		$data = array();

log::info($url);

		foreach ($x->query("//title") as $node)
		{
			@list($junk,$data['NAME'] ) = explode("|", $node->textContent);
			if (empty($data['NAME']))
				$data['NAME'] = $node->textContent;
		}


		$deduper=array();
		$i="";
		foreach ($x->query("//label[contains(@class,'phone')]//following-sibling::span") as $node)
		{
				$deduper[$node->textContent]=1;
		}

		// try a regex
		if (empty($deduper))
		{
			$matches=array();
			preg_match_all("/1?[.\\- ]?\\(?[0-9]{3}[.\\- ]\\)?[0-9]{3}[.\\- ][0-9]{4}/",$html,$matches);
//			log::info($matches);
			if (!empty($matches))
			foreach($matches[0] as $match)
			{
				$deduper[$match]=1;
			}

		}


		foreach($deduper as $phone=>$value)
		{
			//$data = array_merge($data,$pp->parse($node->textContent));
			//log::info($node->c14n());

			$data['PHONE'.$i] = substr( $phone, 0,25);
			$i=empty($i)?$i=1:$i+1; // increment $i if it is empty string, otherwise set to one
			
		}
		

		
		$deduper=array();
		$i="";
		foreach ($x->query("//a[contains(@href,'mailto:')]") as $node)
		{
			$deduper[$node->textContent]=1;
		}

		foreach($deduper as $email=>$value)
		{
			$data['EMAIL'.$i] = $email;
			$i=empty($i)?$i=1:$i+1; // increment $i if it is empty string, otherwise set to one
		}


		$data['WEBSITE'] = $host;
		foreach ($x->query("//div[@id='google-map']//iframe") as $node)
		{
			$src = $node->getAttribute("src");
			$address = urldecode($thiz->urlvar($src,'info'));
		//	log::info($address);

			$data = array_merge($data,$ap->parse($address));
		}
		
		if (sizeof($data) == 2)
		{
			log::info("Loading Different contact page!");
			$link="";
			foreach ($x->query("//a[contains(text(),'contact')]") as $node)
			{
				$link  = $thiz->relative2absolute($url,$node->getAttribute("href"));
				$thiz->loadUrl($link);
			}
			if (!empty($link))
				return;

			$link  = $thiz->relative2absolute($url,"/contact-us");
			$thiz->loadUrl($link);
			return;
		}

//		else
		{
			if (!empty($data))
			{
				unset($data['RAW_ADDRESS']);
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('SOURCE_URL'));	
			}		
		}
	}

	public static function allTheLinks()
	{
		return array('www.faceyfinancial.com/contact/', 'advocisvancouver.advisorwebsite.com/contact/', 'angieyoo2.advisorwebsite.com/contact/', 'asecuredlife.com/contact/', 'davidmutka.advisorwebsite.com/contact/', 'design4advisors.com/contact/', 'mbrockmeyer.advisorwebsite.com/contact/', 'pavlos.advisorwebsite.com/contact/', 'pragfinancial.com/contact/', 'strategicwealthconcepts.netsupsale.com/contact/', 'www.3cwealth.ca/contact/', 'www.4dfinancialadvisors.com/contact/', 'www.abitemi.com/contact/', 'www.acceleratedcg.com/contact/', 'www.adamudy.com/contact/', 'www.advancedwealth.com/contact/', 'www.advisorwebsite.com/contact/', 'www.advocis-toronto.com/contact/', 'www.advociscalgary.ca/contact/', 'www.advocislondon.ca/contact/', 'www.advocisnorthcentralsask.com/contact/', 'www.advocisvancouver.ca/contact/', 'www.aeronevans.com/contact/', 'www.affiancefinancial.com/contact/', 'www.affluence.com/contact/', 'www.albastarache.com/contact/', 'www.albitzmiloe.com/contact/', 'www.aldebaranfinancial.com/contact/', 'www.alexanderpotter.ca/contact/', 'www.alexandriawealth.ca/contact/', 'www.aljones.ca/contact/', 'www.alnagy.com/contact/', 'www.alpoulinfinancialservices.com/contact/', 'www.amgchico.com/contact/', 'www.amy-lee.ca/contact/', 'www.angelobucciarelli.com/contact/', 'www.angelosetten.com/contact/', 'www.anjalijensen.com/contact/', 'www.annecrocker.com/contact/', 'www.annuco.com/contact/', 'www.antilaandassociates.com/contact/', 'www.antonymcaleer.com/contact/', 'www.anushafinancial.com/contact/', 'www.apexfc.com/contact/', 'www.arcacapitalinvestments.com/contact/', 'www.argallus.com/contact/', 'www.ariekorteweg.com/contact/', 'www.arlingtoninvestmentandriskmanagement.com/contact/', 'www.arthursteinfinancial.com/contact/', 'www.asecuredlife.com/contact/', 'www.aspectwealth.com/contact/', 'www.auroratancock.com/contact/', 'www.avenueadvisors.com/contact/', 'www.awrinvestmentmanagement.com/contact/', 'www.bakourisfinancial.com/contact/', 'www.bandobrown.com/contact/', 'www.barrymosher.com/contact/', 'www.bartjagroup.com/contact/', 'www.bastionwealth.ca/contact/', 'www.bayesfinancial.ca/contact/', 'www.baypointwealth.com/contact/', 'www.bbfinancial.ca/contact/', 'www.beaconpoint.ca/contact/', 'www.beckettwm.com/contact/', 'www.bedfordinvestorsgroup.com/contact/', 'www.benefits.wiegers.ca/contact/', 'www.bengenfs.com/contact/', 'www.bertingagnon.com/contact/', 'www.besttaxplan.com/contact/', 'www.bevfinancialgroup.com/contact/', 'www.biddlecapitalmanagement.com/contact/', 'www.bielefeldtfinancial.com/contact/', 'www.billmclaren.ca/contact/', 'www.bltheroux.qc.ca/contact/', 'www.bluestonewa.com/contact/', 'www.bobthompson.ca/contact/', 'www.bondanddevick.com/contact/', 'www.bonniemcphail.com/contact/', 'www.bowenenterprises.com/contact/', 'www.bradcampbell.ca/contact/', 'www.bradswitzer.com/contact/', 'www.brenttodd.ca/contact/', 'www.brianavoss.com/contact/', 'www.brianpeters.ca/contact/', 'www.brownwealth.com/contact/', 'www.brucemckerracher.ca/contact/', 'www.brucemorrison.ca/contact/', 'www.buchholtzfinancial.com/contact/', 'www.business.com/contact/', 'www.capitaledgefinancial.ca/contact/', 'www.carlosrodrigues.ca/contact/', 'www.carlsonasset.com/contact/', 'www.carltoncofinancial.com/contact/', 'www.carrollfinancial.com/contact/', 'www.ceasercapital.com/contact/', 'www.centralparetirementincome.com/contact/', 'www.cfpcorp.net/contact/', 'www.chartwellfinancial.com/contact/', 'www.chrismorgan.ca/contact/', 'www.clafa.net/contact/', 'www.clarityfinancialgroup.com/contact/', 'www.claytonfelladvisors.com/contact/', 'www.clearviewfinancialgroup.com/contact/', 'www.clientfirstria.com/contact/', 'www.cmrfinancialadvisors.com/contact/', 'www.cmwfinancial.ca/contact/', 'www.colefs.com/contact/', 'www.colonialadvisory.com/contact/', 'www.coloradofinancialplanning.com/contact/', 'www.combestfinancialservices.com/contact/', 'www.connieparsons.com/contact/', 'www.conovercapital.com/contact/', 'www.contegocapitalllc.com/contact/', 'www.cooperandrichmond.com/contact/', 'www.coreykudrowich.com/contact/', 'www.cornerstoneassets.net/contact/', 'www.corrystaff.com/contact/', 'www.couloircapital.com/contact/', 'www.courtetravelinsurance.com/contact/', 'www.cowieandassociates.com/contact/', 'www.craievichfinancial.com/contact/', 'www.craiggunn.ca/contact/', 'www.craigkotze.com/contact/', 'www.cramercapitalmanagement.com/contact/', 'www.cranbrookwealth.com/contact/', 'www.dallimoregroup.ca/contact/', 'www.danmcintosh.ca/contact/', 'www.darrenmatwichuk.com/contact/', 'www.daverudofsky.com/contact/', 'www.davidajenson.com/contact/', 'www.davidandreoli.com/contact/', 'www.davidfeldberg.ca/contact/', 'www.davidhames.ca/contact/', 'www.davidpescod.com/contact/', 'www.daviesmahnke.com/contact/', 'www.dbpaustin.com/contact/', 'www.dcigroup.ca/contact/', 'www.deanbarth.com/contact/', 'www.debbielang.com/contact/', 'www.debbiemacintosh.com/contact/', 'www.decolson.com/contact/', 'www.dekkerhewettgroup.com/contact/', 'www.design4advisors.com/contact/', 'www.diagnosemywealth.com/contact/', 'www.dinuzzo.com/contact/', 'www.discoveryfinancial.ca/contact/', 'www.diversico.ca/contact/', 'www.divinefinancial.ca/contact/', 'www.dominion-partners.com/contact/', 'www.doncromar.com/contact/', 'www.donfox.net/contact/', 'www.douggiffen.net/contact/', 'www.douglassfinancialgroup.com/contact/', 'www.dreamcatcherwealthmanagement.com/contact/', 'www.drlgroup.net/contact/', 'www.duanebentley.ca/contact/', 'www.duncanrobinson.com/contact/', 'www.duncanwade.com/contact/', 'www.dwlfinancial.com/contact/', 'www.easternplanning.com/contact/', 'www.elienafekh.com/contact/', 'www.eloisearlint.com/contact/', 'www.encompasswealth.ca/contact/', 'www.engwallclark.com/contact/', 'www.engwallclarkandassociates.com/contact/', 'www.entrustfinancialservices.biz/contact/', 'www.equisgroup.ca/contact/', 'www.ericdbrown.ca/contact/', 'www.ericksonadvisors.net/contact/', 'www.ericrichards.ca/contact/', 'www.evergreenwealthadvisors.com/contact/', 'www.exemplarfn.com/contact/', 'www.exemplarfnadvisor.com/contact/', 'www.eyfinancial.ca/contact/', 'www.f55fcalgary.com/contact/', 'www.f55fnorthernontario.com/contact/', 'www.fayeafshar.com/contact/', 'www.fdgteam.com/contact/', 'www.ffmonline.net/contact/', 'www.fidwealthman.com/contact/', 'www.financemontreal.com/contact/', 'www.financialalliance.ca/contact/', 'www.financialfiduciariesllc.com/contact/', 'www.financialgroup.com/contact/', 'www.financialguidancepc.com/contact/', 'www.financialwomenabq.com/contact/', 'www.flynnzito.com/contact/', 'www.focalpointcoachingcanada.ca/contact/', 'www.focalpointcoachkelly.com/contact/', 'www.foresightfinancial.com/contact/', 'www.forsythsecuritiesinc.com/contact/', 'www.fortressfinancialstrategies.com/contact/', 'www.fosterassoc.com/contact/', 'www.foundationpwm.com/contact/', 'www.fountainfinancial.net/contact/', 'www.foxpurlee.com/contact/', 'www.frcfinancial.net/contact/', 'www.frush.com/contact/', 'www.fsaadvisors.com/contact/', 'www.futurevalues.com/contact/', 'www.gaetangoudreau.com/contact/', 'www.garthbush.com/contact/', 'www.generisfinancial.net/contact/', 'www.genierfinancial.com/contact/', 'www.geoffcarli.com/contact/', 'www.giainvestmentadvisors.com/contact/', 'www.gillesmorin.net/contact/', 'www.gillriefinancial.com/contact/', 'www.girishagrawal.com/contact/', 'www.govic.com/contact/', 'www.gpsfinancialgroup.ca/contact/', 'www.grahamwealth.com/contact/', 'www.grandviewfinancialgroup.com/contact/', 'www.grantlaube.com/contact/', 'www.greatfutures.com/contact/', 'www.gregclancy.net/contact/', 'www.gregdesimone.com/contact/', 'www.gregmorrow.net/contact/', 'www.groupeinvestorsboisbriand.com/contact/', 'www.groupeinvestorslebourgneuf.ca/contact/', 'www.groupeinvestorsmontrealwestisland.ca/contact/', 'www.groupeinvestorsmtlrivesud.com/contact/', 'www.groupeinvestorsquebecrivesud.com/contact/', 'www.groupeinvestorsquebecsillery.com/contact/', 'www.groupeinvestorssherbrooke.ca/contact/', 'www.groupeinvestorswestmount.com/contact/', 'www.grsfs.com/contact/', 'www.grupofinancial.com/contact/', 'www.habitsofwealth.com/contact/', 'www.hallburns.com/contact/', 'www.hankinpatentlaw.com/contact/', 'www.harborfinancialtexas.com/contact/', 'www.harborws.com/contact/', 'www.harvestwealthmanagement.ca/contact/', 'www.hayhoeteam.com/contact/', 'www.helenokeefe.com/contact/', 'www.helkiefinancial.com/contact/', 'www.hemenwaytax.com/contact/', 'www.henrygriffioen.com/contact/', 'www.herplamba.com/contact/', 'www.hfwm.ca/contact/', 'www.highlanderfinancialgroup.com/contact/', 'www.highlandfinancial.bizwww.hoesgen.com/contact/', 'www.hoesgen.com/contact/', 'www.hohmannfinancialgroup.com/contact/', 'www.hollettlanger.com/contact/', 'www.hstephensgroup.com/contact/', 'www.hugheshfs.com/contact/', 'www.hutchinsonwealth.com/contact/', 'www.ianmoyer.com/contact/', 'www.ianxenias.com/contact/', 'www.indepthplan.com/contact/', 'www.insidersforum.com/contact/', 'www.insurancelinkatlantic.com/contact/', 'www.insurecollc.com/contact/', 'www.investment-options.ca/contact/', 'www.investments-financialplanning.com/contact/', 'www.investorsgroupbellevillecobourg.com/contact/', 'www.investorsgroupcalgaryfoothills.com/contact/', 'www.investorsgroupdartmouth.com/contact/', 'www.investorsgroupdurham.com/contact/', 'www.investorsgrouphalifax.com/contact/', 'www.investorsgroupkingston.com/contact/', 'www.investorsgroupmoncton.com/contact/', 'www.investorsgroupmontrealwestisland.ca/contact/', 'www.investorsgroupnorthvancouver.com/contact/', 'www.investorsgroupnorthyork.com/contact/', 'www.investorsgrouponwaverley.com/contact/', 'www.investorsgroupstjohns.com/contact/', 'www.investorsgrouptorontoyorkdale.com/contact/', 'www.investorsgroupupperyork.com/contact/', 'www.investorsgroupvictoriadowntown.com/contact/', 'www.investorsgroupwestmount.com/contact/', 'www.investorsgroupwinnipegdowntown.com/contact/', 'www.ipstrategies.ca/contact/', 'www.ironhorseinvest.com/contact/', 'www.jacksonfs.com/contact/', 'www.jamesdarnell.ca/contact/', 'www.jamieernst.com/contact/', 'www.jeanfrancoisgirard.ca/contact/', 'www.jeanmauricevezina.ca/contact/', 'www.jeffcallaway.com/contact/', 'www.jeffnielsen.focalpointcoaching.com/contact/', 'www.jenniferchristensen.com/contact/', 'www.jfthompsonwealthmanagement.com/contact/', 'www.jimgarrisonfinancial.com/contact/', 'www.jimszilva.com/contact/', 'www.jindalfinancial.com/contact/', 'www.jmforney.com/contact/', 'www.jodiweber.com/contact/', 'www.johansonfinancial.com/contact/', 'www.johncutlercoaching.com/contact/', 'www.johndruce.com/contact/', 'www.johngilchrist.ca/contact/', 'www.johnmazziotti.com/contact/', 'www.jonescapitalmanagement.com/contact/', 'www.joseehoule.com/contact/', 'www.jtfinancialsolutions.com/contact/', 'www.juliewhitely.com/contact/', 'www.karasickandassociates.com/contact/', 'www.karinoreilly.com/contact/', 'www.kellyhemmett.com/contact/', 'www.kelticfinancialgroup.com/contact/', 'www.kenhendriks.com/contact/', 'www.kevanmccarthy.com/contact/', 'www.kevinking.ca/contact/', 'www.keyadvisorsgroupllc.com/contact/', 'www.knightsfinancialmanagement.ca/contact/', 'www.koenigrusso.com/contact/', 'www.kornerstone401k.com/contact/', 'www.kramerinvest.com/contact/', 'www.kslater.ca/contact/', 'www.lakelsefinancial.com/contact/', 'www.lamiafinancial.com/contact/', 'www.landmarkfn.com/contact/', 'www.lasuitelogique.com/contact/', 'www.laurabelleau.com/contact/', 'www.laurenthugron.com/contact/', 'www.lcptactical.com/contact/', 'www.leblancgroup.ca/contact/', 'www.leemgt.com/contact/', 'www.lehmancap.com/contact/', 'www.lexwealth.com/contact/', 'www.lifebp.com/contact/', 'www.lighthousefinancialplanning.com/contact/', 'www.ljcinsurance.com/contact/', 'www.llfinancialgroup.com/contact/', 'www.lmwealth.com/contact/', 'www.low-stress-investing.com/contact/', 'www.lucfilion.com/contact/', 'www.lyfestyle.ca/contact/', 'www.lylekarasick.com/contact/', 'www.mackiewealth.com/contact/', 'www.mainlinewealthmanagement.com/contact/', 'www.maleckifinancialgroup.com/contact/', 'www.maltinwealth.com/contact/', 'www.managed-wealth.com/contact/', 'www.mangrovefinancialgroup.com/contact/', 'www.mannfinancialassurance.com/contact/', 'www.margaretmaclay.focalpointcoaching.com/contact/', 'www.marianhundt.ca/contact/', 'www.maritimewealth.com/contact/', 'www.markdonaldsonfinancial.com/contact/', 'www.markewert.com/contact/', 'www.markmazurek.com/contact/', 'www.markopocedic.com/contact/', 'www.markvanhees.com/contact/', 'www.markwoolnough.com/contact/', 'www.martinboileau.com/contact/', 'www.martinleduc.net/contact/', 'www.maryrobertson.net/contact/', 'www.masonfinancial.ca/contact/', 'www.matthewcano.com/contact/', 'www.mayfinancialplanning.com/contact/', 'www.mccoysolutions.net/contact/', 'www.mcdanielfinancial.com/contact/', 'www.mcwealthmgt.com/contact/', 'www.mcwilliamfinancial.com/contact/', 'www.mehulgandhi.com/contact/', 'www.melissaeyben.com/contact/', 'www.menziesfinancial.ca/contact/', 'www.michaelbrewitt.com/contact/', 'www.michaelcollymore.com/contact/', 'www.michaelhoffman.ca/contact/', 'www.miig.biz/contact/', 'www.miig.bizwww.milesschiller.com/contact/', 'www.mikealpert.com/contact/', 'www.mikewoods.ca/contact/', 'www.milesschiller.com/contact/', 'www.milestoneasset.ca/contact/', 'www.millennialgroup.us/contact/', 'www.millennialgroup.uswww.mitchellwealth.com/contact/', 'www.minertfinancial.com/contact/', 'www.mitchchilds.com/contact/', 'www.mitchellwealth.com/contact/', 'www.mitrovicinvestmentgroup.com/contact/', 'www.moffattfinancial.com/contact/', 'www.moneyfingroup.com/contact/', 'www.moorerobertsandassociates.com/contact/', 'www.morleynadeau.com/contact/', 'www.mpactfinancial.com/contact/', 'www.mpsinsurance.ca/contact/', 'www.mpwealthadvisory.com/contact/', 'www.murdockbanner.com/contact/', 'www.murphyneil.ca/contact/', 'www.murrayparks.com/contact/', 'www.musifinancial.com/contact/', 'www.muskokafinancial.com/contact/', 'www.myeecuinvestments.org/contact/', 'www.myfhf.com/contact/', 'www.myfinancialsense.com/contact/', 'www.nancykirkham.com/contact/', 'www.nathangarries.com/contact/', 'www.navigator-financial.com/contact/', 'www.neilandassociates.ca/contact/', 'www.newerafinancialstrategies.com/contact/', 'www.nextlevelinvestor.net/contact/', 'www.niagarainvestorsgroup.com/contact/', 'www.nickpapapanos.com/contact/', 'www.nickparnell.ca/contact/', 'www.nielsonwealth.com/contact/', 'www.nollettefinancial.com/contact/', 'www.northranchfinancialgroup.com/contact/', 'www.northwestfinancial.net/contact/', 'www.npcofinancial.com/contact/', 'www.nyqgroup.com/contact/', 'www.onelifefg.com/contact/', 'www.ontariohealthquotes.ca/contact/', 'www.oureoa.com/contact/', 'www.pacificvalue.net/contact/', 'www.parinvestfinancial.com/contact/', 'www.parkerfeeonly.com/contact/', 'www.patricebeaudoin.ca/contact/', 'www.patriciabell.ca/contact/', 'www.patriciajacob.ca/contact/', 'www.paul-smith.ca/contact/', 'www.paulborisoff.com/contact/', 'www.paulgowan.com/contact/', 'www.peakfinancial.net/contact/', 'www.pekteam.com/contact/', 'www.pennijohnston.com/contact/', 'www.peteaarssen.com/contact/', 'www.petermullins.net/contact/', 'www.pfstl.org/contact/', 'www.phase4fs.com/contact/', 'www.philbloom.ca/contact/', 'www.philgilkes.focalpointcoaching.com/contact/', 'www.pillarfp.com/contact/', 'www.pioneerfinancialplanning.com/contact/', 'www.planningworks.biz/contact/', 'www.premierstrategy.ca/contact/', 'www.prenticelee.ca/contact/', 'www.privateocean.com/contact/', 'www.proactivefinancial.com/contact/', 'www.proactiveinvest.com/contact/', 'www.prosperitywealth.ca/contact/', 'www.prosperityworks.com/contact/', 'www.protectionforlife.ca/contact/', 'www.puhlbenefits.com/contact/', 'www.pyramidwealth.ca/contact/', 'www.quantumecon.com/contact/', 'www.quebecsaguenay.com/contact/', 'www.rajivbhasin.com/contact/', 'www.ralphwinn401k.com/contact/', 'www.randymarvell.com/contact/', 'www.raydallaire.com/contact/', 'www.rdrfinancial.com/contact/', 'www.regalfin.com/contact/', 'www.regalria.com/contact/', 'www.reinehebert.com/contact/', 'www.rejstringer.ca/contact/', 'www.reynoldsinglis.ca/contact/', 'www.richardborland.com/contact/', 'www.richardkemp.ca/contact/', 'www.richardkilburn.com/contact/', 'www.richardkortje.com/contact/', 'www.richiegroup.ca/contact/', 'www.richscott.ca/contact/', 'www.richter-financial.com/contact/', 'www.rickspence.biz/contact/', 'www.rlawealth.com/contact/', 'www.robertcranbury.com/contact/', 'www.robertforschner.focalpointcoaching.com/contact/', 'www.robgare.com/contact/', 'www.robrobinson.ca/contact/', 'www.robsavosardaro.com/contact/', 'www.rockportwealth.com/contact/', 'www.rodneyblake.com/contact/', 'www.rohinikapoor.com/contact/', 'www.ronaldwing.com/contact/', 'www.rosselliott.ca/contact/', 'www.rossmckenzie.net/contact/', 'www.rthomasarmstrong.com/contact/', 'www.rtwealth.com/contact/', 'www.rudichukagencies.com/contact/', 'www.rupertwhiting.focalpointcoaching.com/contact/', 'www.russmceachnie.com/contact/', 'www.rwadvisors.com/contact/', 'www.ryanbudd.ca/contact/', 'www.safranwealthadvisors.com/contact/', 'www.sampsoncapital.com/contact/', 'www.sandraramos.ca/contact/', 'www.schultzfinancial.com/contact/', 'www.scottmccartney.com/contact/', 'www.seaboardadvisors.com/contact/', 'www.seantidd.com/contact/', 'www.seasonsfinancialgroup.com/contact/', 'www.sebastienchevrier.ca/contact/', 'www.secureannuityadvisors.com/contact/', 'www.secureretirement.ca/contact/', 'www.sfsplanners.com/contact/', 'www.shafikhirani.com/contact/', 'www.sharebuilder.com/contact/', 'www.sharonkinsman.com/contact/', 'www.shawnrutledge.com/contact/', 'www.shinskefinancialservices.com/contact/', 'www.shirleyhill.net/contact/', 'www.signupforconference.com/contact/', 'www.simonhiebert.com/contact/', 'www.simonkswong.com/contact/', 'www.sksfinancial.com/contact/', 'www.soundretirementadvisors.com/contact/', 'www.southeasternplanningconsultants.com/contact/', 'www.stephaneturmel.net/contact/', 'www.stephencragg.com/contact/', 'www.stephenpustai.com/contact/', 'www.sterlingretirement.com/contact/', 'www.stevelloyd.ca/contact/', 'www.strategicwealthconcepts.net/contact/', 'www.stravolo.com/contact/', 'www.streatchwealth.com/contact/', 'www.strongpracticesolutions.com/contact/', 'www.successfulventurescoaching.com/contact/', 'www.supruninvestment.com/contact/', 'www.surepathgroup.ca/contact/', 'www.sylviestewart.com/contact/', 'www.synergyfinancialgroup.com/contact/', 'www.syrja.ca/contact/', 'www.tagecawley.com/contact/', 'www.tanicetaylor.com/contact/', 'www.taylorfinancial.ca/contact/', 'www.tbrooksfinancial.com/contact/', 'www.tedesco.org/contact/', 'www.telesisfinancial.com/contact/', 'www.teresahatto.com/contact/', 'www.teska.ca/contact/', 'www.tevebaughandassociates.com/contact/', 'www.texas.focalpointcoaching.com/contact/', 'www.the-benefits-guy.ca/contact/', 'www.thebusinessspotlightinc.com/contact/', 'www.thefinitygroup.com/contact/', 'www.thehallgroup.ca/contact/', 'www.thetallmangroup.com/contact/', 'www.thevaultstrategy.com/contact/', 'www.thistlefinancial.net/contact/', 'www.timchimuk.com/contact/', 'www.timdoyle.ca/contact/', 'www.timsallee.com/contact/', 'www.tkatchthetrend.com/contact/', 'www.toddheard.com/contact/', 'www.toddmorin.com/contact/', 'www.tomburke.ca/contact/', 'www.tomcoxgroup.com/contact/', 'www.tommihaljevic.com/contact/', 'www.tonymaduri.com/contact/', 'www.tonyrogers.ca/contact/', 'www.trentmackeen.com/contact/', 'www.trevorklassen.com/contact/', 'www.trudybutt.com/contact/', 'www.truenorthadvisors.orgwww.ulivi.com/contact/', 'www.truvisionfinancial.com/contact/', 'www.turningpointewealthmanagement.com/contact/', 'www.tylerzaba.com/contact/', 'www.ulivi.com/contact/', 'www.vanfraserfinancial.ca/contact/', 'www.vanmetro.ca/contact/', 'www.velawealth.com/contact/', 'www.verusfp.com/contact/', 'www.victoradair.com/contact/', 'www.victoriadowntowninvestorsgroup.com/contact/', 'www.vlpfa.com/contact/', 'www.wainstushnoff.com/contact/', 'www.waitefinancial.com/contact/', 'www.walteralonso.com/contact/', 'www.waynecadogan.com/contact/', 'www.wayneelliott.ca/contact/', 'www.wdba.ca/contact/', 'www.wealthplanadvisorsgroup.com/contact/', 'www.wealthrochester.com/contact/', 'www.wealthsensefinancial.com/contact/', 'www.wealthstrategies.com/contact/', 'www.webvisorbook.com/contact/', 'www.weingartenassociates.com/contact/', 'www.wellspringfmt.ca/contact/', 'www.wendystrub.com/contact/', 'www.werthfinancial.com/contact/', 'www.westtexasinvestments.com/contact/', 'www.whitehavenwealth.ca/contact/', 'www.whitehawkadvisory.com/contact/', 'www.wiegersbenefits.com/contact/', 'www.wiegersfinancial.com/contact/', 'www.wilimek.com/contact/', 'www.windsorwealthplanning.com/contact/', 'www.wintherassetmanagement.com/contact/', 'www.wm-partners.com/contact/', 'www.wnwealth.ca/contact/', 'www.wolfsonfinancial.ca/contact/', 'www.yourpartnerinbenefits.com/contact/', 'www.zanehandysides.com/contact/', 'www.zfinancialplanning.com');
	}
}

$r= new advisorwebsites();
$r->parseCommandLine();

