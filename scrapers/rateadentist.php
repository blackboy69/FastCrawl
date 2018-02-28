<?
include_once "config.inc";
error_reporting(E_ALL);

class rateadentist extends baseScrape
{
	public static $_this=null;
	public $isPost = true;

	public function runLoader()
	{
		
		$type = get_class();		
		/*

		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		db::query("DROP TABLE $type");
		#db::query("DELETE FROM $type");
		
		
		*/
		unlink($this->cookie_file);
		$this->cookies=true;
		//$this->proxy = "localhost:8888";
		$this->noProxy=true;
			;
		$this->threads=1;
		
		$this->debug=false;
		//		log::$errorLevel = ERROR_ALL;

/*		db::query("DELETE FROM load_queue where type='$type' ");
		db::query("DELETE FROM raw_data where type='$type' ");*/
//		db::query("DROP TABLE $type");


		//$zips = $this->newZips; //	array_merge($this->newZips, db::oneCol("SELECT distinct zip FROM geo.locations where pop > 2499 and zip > 99901"));
//$this->zips=array(19312);

$zips = array_merge($this->newZips, db::oneCol("SELECT distinct zip FROM geo.locations where pop > 20999 "));//and  zip > 96706 "));
		$i=0;
		foreach ( $zips as $zip)
		{
			log::info($zip);
			$url = "http://www.rateadentist.com/";
			$html = $this->Get($url);
			$formParser = new HtmlParser($html);
			$f = $formParser->GetForm();
			$data = array_merge($f[0], $formParser->loadJavaXViewState());	

			$queryString = "homePageWelcomeDoSearchForm%3AcityState=".$zip."&org.apache.myfaces.trinidad.faces.FORM=".urlencode($data["org.apache.myfaces.trinidad.faces.FORM"])."&_noJavaScript=false&javax.faces.ViewState=".urlencode($data['javax.faces.ViewState'])."&source=homePageWelcomeDoSearchForm%3AwelcomeDoSearchAction";

			$url  = "http://www.rateadentist.com".$data['action'];
			$this->setReferer($url, "http://www.rateadentist.com/");
			$html = $this->Post($url, $queryString);
			$type = get_class();		
			$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
			$dom = new DOMDocument();
			
			$formParser = new HtmlParser($html);
			$f = $formParser->GetForm();
			$data = array_merge($f[0], $formParser->loadJavaXViewState());	


			$x = new Xpath($html);	
			$ap = new Address_Parser();
			$oh = new Operating_Hours_Parser(); 
			$found  = false;
			$thiz=self::getInstance();
			
			// load the rating
			foreach ($x->query("//form") as $f)
			{
				$form = $f->getAttribute("id");
				$action = $f->getAttribute("action");

			}
			$viewstate = $x->loadJavaXViewState();	
				$j=0;
			foreach ($x->query("//li[@class='separaters' or @class='separaterspremium']") as $listing)
			{
				$data=array();	

				if ($listingArray = explode("<br>", $listing->c14n()))
				{

					if (sizeof($listingArray)>3)
					{
						$data["Name"] = trim(strip_tags($listingArray[0]));
						$address = join(",", array($listingArray[1], $listingArray[2]));
						$data['Phone'] = preg_replace("/<.+>/u", "", $listingArray[3]);
						$data = array_merge($data,$ap->parse($address));
						
						for($i=4;$i<sizeof($listingArray);$i++)
						{
							$itm = $listingArray[$i];

							if(preg_match("/@/",$itm))
							{
								$data["E-Mail"] =  preg_replace("/<.+>/u", "",$itm);
							}
							else if(preg_match("/http/",$itm))
							{
								$data["Website"] = trim(strip_tags($itm));
							}
							else if (preg_match("/dentist/i",$itm))
							{	
								$data['Services Offered'] = $itm;
							}
							else if(preg_match("/lish/",$itm))
							{
								$data['Languages Spoken'] = trim(strip_tags($itm));
							}
							else if(preg_match("/ratings/",$itm))
							{
								$data['Ratings'] = trim(strip_tags($itm));
							}
						}

						

						$source = "officeVOArray:$j:searchResultsViewAction";
						$j++;

						$queryString = "org.apache.myfaces.trinidad.faces.FORM=".urlencode($form)."&_noJavaScript=false&javax.faces.ViewState=".urlencode($viewstate['javax.faces.ViewState'])."&source=".urlencode($source);
						
						if (! empty($data['Ratings']) && trim($data['Ratings']) != 'No ratings yet' )
						{
							$details = $thiz->Post("http://rateadentist.com/$action", $queryString);
							
							$ratingDate = array();
							$x = new Xpath($details);
							foreach($x->query("//span[contains(@class,'dtReviewed')]") as $node)
							{
								$ratingDate[] = strtotime(str_replace("-","/", $node->textContent));
							}

							if (sizeof($ratingDate))
							{
								rsort($ratingDate);
								$data['MOST_RECENT_RATING'] =  strftime("%m-%d-%Y", $ratingDate[0]);
							}
						}
						//	log::info(db::normalize($data));
						//if (isset($data['MOST_RECENT_RATING']))
						{
							log::info(db::normalize($data));			
							$id = db::store($type,$data,array('Name','Phone'));
						}

					}
				}
			}
		}	

		
	}
	var $newZips = array ( /*'94928','01701', '01720', '01844', '01886', '01887', '02052', '02169', '02190', '02459', '02478', '02532', '02915', '03062', '03431', '04105', '04401', '07645', '10022', '10514', '10528', '10968', '01106', '11743', '11776', '11787', '12047', '12528', '13031', '13069', '13090', '13905', '14051', '14420', '14424', '14534', '14616', '14623', '15126', '15146', '15213', '15237', '15301', '15317', '16066', '16901', '17013', '17112', '17837', '18036', '18704', '18940', '19008', '19063', '19320', '19335', '19342', '19348', '19380', '19422', '19438', '19454', '19460', '19530', '19702', '19707', '19709', '19901', '19963', '20016', '20132', '20147', '20165', '20721', '20871', '20905', '21009', '21015', '21030', '21113', '21114', '21117', '21210', '21401', '21784', '21801', '22030', '22180', '22192', '22311', '22401', '22554', '22601', '22901', '23116', '23455', '23927', '24210', '24401', '24701', '25177', '25504', '25526', '26038', '27203', '27302', '27408', '27513', '27526', '27545', '27549', '27587', '27597', '27615', '27617', '27704', '28001', '28031', '28083', '28104', '28110', '28115', '28117', '28207', '28212', '28217', '28273', '28403', '28411', '28546', '28557', '28637', '28677', '28732', '28786', '28803', '29150', '29223', '29316', '29464', '29492', '29501', '29607', '29609', '29621', '30014', '30017', '30019', '30022', '30024', '30032', '30041', '30043', '30047', '30062', '30087', '30092', '30101', '30120', '30135', '30188', '30214', '30236', '30327', '30329', '30342', '30506', '30538', '30566', '30701', '30907', '31032', '31088', '31210', '31757', '31794', '32003', '32068', '32174', '32205', '32223', '32256', '32308', '32404', '32405', '32456', '32547', '32725', '32746', '32763', '32765', '32771', '32789', '32837', '32908', '32960', '33014', '33063', '33131', '33181', '33308', '33317', '33326', '33543', '33609', '33710', '33765', '33772', '33813', '34109', '34237', '34428', '34448', '34484', '34638', '34667', '34711', '35010', '35022', '35124', '35209', '35404', '35630', '35758', '36303', '36532', '36695', '36830', '37064', '37076', '37129', '37167', '37203', '37205', '37212', '03766', '37771', '37830', '37885', '37918', '38125', '38134', '03818', '03820', '38478', '39216', '39301', '40744', '42345', '04240', '43017', '43026', '43055', '43119', '44057', '44092', '44116', '44122', '44124', '44143', '44145', '44212', '44278', '44484', '44687', '44718', '44870', '45042', '45133', '45212', '45231', '4530', '45324', '45449', '45750', '46112', '46135', '46391', '46514', '46635', '46952', '47201', '47240', '47362', '47711', '47905', '47978', '48076', '48085', '48116', '48146', '48152', '48183', '48187', '48326', '48346', '48360', '48375', '48381', '48439', '48603', '48864', '48910', '49024', '49085', '49247', '49306', '49332', '49348', '49401', '49424', '49444', '49855', '50226', '50309', '50323', '50677', '53024', '53115', '53151', '53186', '53226', '53562', '53565', '53718', '53818', '54017', '54115', '54313', '54401', '54455', '54650', '54701', '55016', '55057', '55101', '55116', '55364', '55391', '55405', '55811', '55975', '56001', '56024', '56073', '56377', '05661', '57717', '60010', '60045', '60061', '60062', '60108', '60123', '60187', '60188', '06019', '60193', '06040', '60448', '60506', '60521', '60538', '60544', '60602', '60630', '60647', '60655', '06095', '61350', '61761', '61820', '62034', '62220', '06226', '06260', '63011', '63017', '63044', '63080', '63123', '63126', '63703', '64024', '64063', '64093', '06410', '64114', '64116', '64145', '64158', '06422', '06443', '06460', '06468', '06512', '06514', '65721', '06615', '67010', '67205', '67401', '67460', '06801', '06840', '68502', '06877', '06880', '68845', '07003', '70065', '70115', '70301', '7039', '70458', '70508', '07054', '70767', '07095', '71105', '71111', '71270', '72034', '72401', '72703', '73099', '73115', '73120', '73170', '73801', '74006', '74055', '74135', '7470', '75024', '75028', '75032', '75061', '75069', '75077', '75080', '75093', '75104', '75149', '75254', '76015', '76049', '76051', '76063', '76065', '76108', '76132', '76133', '76137', '76179', '76227', '76308', '7645', '76634', '76710', '07702', '77060', '77063', '77065', '07717', '07731', '77327', '77357', '77379', '77380', '07747', '77479', '77521', '77845', '78045', '78052', '78229', '78251', '78602', '78628', '78634', '78640', '78660', '7869', '78734', '78745', '7882', '79416', '79424', '79701', '79761', '80016', '08002', '80104', '80122', '80132', '80221', '08026', '80302', '80401', '80501', '80524', '80528', '08055', '08087', '80917', '81008', '81082', '81637', '82070', '82601', '82930', '83201', '83263', '83301', '83318', '83401', '83647', '83709', '83814', '84041', '84084', '84120', '84124', '84403', '85020', '85040', '85048', '85119', '85224', '85225', '85249', '85251', '85296', '85338', '85374', '85541', '86336', '87031', '87120', '8753', '8816', '8822', '8831', '89014', '89101', '89134', '89144', '89519', '89521', '90232', '90266', '90501', '90505', '90601', '90815', '91107', '91355', '91505', '91711', '91752', '91773', '91786', '91910', '92024', '92025', '92026', '92056', '92064', '92075', '92101', '92111', '92277', '92373', '92506', '92508', '92592', '92648', '92672', '92683', '92780', '93003', '93010', '93041', '93065', '93117', '93245', '93257', '93309', '93455', '93534', '93720', '93901', '94015', '94040', '94132', '94401', '94510', '94513', '94530', '94545', '94546', '94550', '94583', '94596', '94598', '95050', '95062', '95124', '95127', '95207', '95336', '95376', '95382', '95409', '95630', '95662', '95687', '95758', '95825', '96001', '96080', */'96720', '96732', '96744', '96813', '96814', '97005', '97048', '97202', '97330', '97365', '97401', '97701', '97756', '98012', '98034', '98057', '98208', '98273', '98311', '98339', '98374', '98662', '98683', '98686', '99163', '99218', '99301', '99352');
	
	

}

$r = new rateadentist();
$r->parseCommandLine();