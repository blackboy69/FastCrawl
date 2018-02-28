<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";
/*
Contractor's License Detail 

*/
class cslb_ca_gov extends baseScrape
{
    public static $_this=null;
	var $workChunkSize = 1000;

	public function __construct()
	{
		//R::freeze( true );
		parent::__construct();
	}
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=true;
		$this->allowRedirects = false;
		$this->threads=2;
		$this->debug=false;
			//$this->clean(true);
		$toLoad = array();
		log::info("start  long update");
	//	db::query("UPDATE raw_data set parsed = 0 where parsed = 1 and url like 'https://www2.cslb.ca.gov/OnlineServices/CheckLicenseII/LicenseDetail.aspx?LicNum=%' and type='$type' ");
		log::info("done long update");
				
		// This takes a long time ! queuing over 1 million requests!
		/*
		for($i=10000;$i<1250000;$i++)
		{			
			$toLoad[] = "https://www2.cslb.ca.gov/OnlineServices/CheckLicenseII/LicenseDetail.aspx?LicNum=".$i;
		}
		
		$this->loadUrlsByArray($toLoad);
		*/
		//$this->loadUrlsByArray($toLoad);
		
		//$this->loadUrl("https://www2.cslb.ca.gov/OnlineServices/CheckLicenseII/LicenseDetail.aspx?LicNum=278116");
		//$this->loadUrl("https://www2.cslb.ca.gov/OnlineServices/CheckLicenseII/LicenseDetail.aspx?LicNum=278116sadfsadf");
		//RB::freeze();
		
	}
	/*
	public function csvSql()
	{
		$type = get_class($this);
		
		$states = db::oneCol("SELECT distinct state from geo.locations");
		$csvSql["$type.1"] = "SELECT * from $type LIMIT 0,250000";
		$csvSql["$type.2"] = "SELECT * from $type LIMIT 250000,250000";
		$csvSql["$type.3"] = "SELECT * from $type LIMIT 50000000,500000";

		return $csvSql;
		
	}
*/

	public static function parse($url,$html)
	{
		
		//log::info("Parsing $url");
		//echo ".";
		if (strlen($html) < 500 )
		{
			// make this go way faster so we don't have to parse yet again!
			
			//not found
		//	log::info("not found");
			return;
		}
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 
		
		
				
		$type = get_class();		
		$thiz = self::getInstance();
		
		$x = new XPath($html);	
		$data = array();
		$kvp = new KeyValue_Parser();
		$ap = new Address_Parser();
		$pp = new Phone_Parser();
		
		$timer = new timer();
		$timer->start();
		
		$LicNum = $query['LicNum'];
		$data['LICNUM'] = $LicNum;
		
		//R::freeze( false );				
				
		if (preg_match("#LicenseDetail.aspx#",$url))
		{		
			// l0ok for nexct page link
			foreach($x->query("//td[@id='ctl00_LeftColumnMiddle_BusInfo']") as $node)
			{
				$info = explode("<br>",$node->c14n() );			
			}
			
			$data['COMPANY'] = trim(strip_Tags(html_entity_decode(array_shift($info))));
			$data = array_merge($data,$ap->parse(join(",", $info) ));
			$data = array_merge($data,$pp->parse(join(",", $info) ));
		
		
			foreach($x->query("//td[@id='ctl00_LeftColumnMiddle_Entity']") as $node) {
				$data['ENTITY'] = $node->textContent;
			}
			
			foreach($x->query("//td[@id='ctl00_LeftColumnMiddle_IssDt']") as $node) {
				$data['ISSUE_DATE'] = $node->textContent;
			}
			
			foreach($x->query("//td[@id='ctl00_LeftColumnMiddle_ExpDt']") as $node) {
				$data['EXPIRE_DATE'] = $node->textContent;
			}
			
			foreach($x->query("//td[@id='ctl00_LeftColumnMiddle_Status']//span") as $node){
				$data['STATUS'] = $node->textContent;
			}
				
			foreach($x->query("//a[contains(@href,'INSDetail.aspx')]") as $node){
				$data['BOND_BANK'] = $node->textContent;
			}
					
			foreach($x->query("//td[@id='ctl00_LeftColumnMiddle_BondingCellTable']") as $node){
				$data = array_mergE($data, $kvp->parse( $node->textContent));
			}
			if (isset($data['EFFECTIVE_DATE']))
			{
				$data['BOND_EFFECTIVE_DATE'] = str_replacE("Contractor's Bond History","",$data['EFFECTIVE_DATE']);
				unset($data['EFFECTIVE_DATE']);
			}
			
			if (isset($data['CANCELLATION_DATE']))
			{
				$data['BOND_CANCELLATION_DATE'] = str_replacE("Contractor's Bond History","",$data['CANCELLATION_DATE']);				
				$data['BOND_CANCELLATION_DATE'] = str_replacE("Bond of Qualifying Individual","",$data['BOND_CANCELLATION_DATE']);			
				unset($data['CANCELLATION_DATE']);			
			}
			
			$data['SOURCE_URL']  = $url;
			
			$i="";
			foreach ($x->query("//td[@id='ctl00_LeftColumnMiddle_ClassCellTable']//a") as $node){
				
				$data['CLASSIFICATION'.$i++] = $node->textContent;
			}
			
			$thiz->loadUrl("https://www2.cslb.ca.gov/OnlineServices/CheckLicenseII/PersonnelList.aspx?LicNum=$LicNum&LicName=".urlencode($data['COMPANY']));
			
			log::info($data['LICNUM']);	
			$timer->checkpoint("Parsed 1 row ");
								
			//echo " ".$data['LICNUM']. ">";
			try 
			{
				db::store($type,$data,array('LICNUM'),true);	
				
				$timer->checkpoint("Saved 1 row ");
				


			}
			catch (exception $ex)
			{
				log::error($ex);
			}
			
		}
		else if (preg_match("#/PersonnelList.aspx#",$url))
		{			
			$d=array();
			$i="";				
			foreach($x->query("//table[@id='ctl00_LeftColumnMiddle_Table1']//tr//tr") as $node){
				$line = strip_tags(str_replace("</td><td>",": ", $node->c14n()));
				$d = $kvp->parse( $line);			
				$k = array_keys($d);
				$k = $k[0];
				
				if ($k == "NAME")
					$i++;				
				
				if($i>5)
					break; // don't want too many of these
				
				$data[$k.$i] = $d[$k];		
				$data[$k] = $d[$k];										
			}
			log::info(" ". $data['LICNUM']."=> ($i) ".@$data['NAME']);
			//log::info("---");
			//log::info($data);		
			$timer->checkpoint("Parsed 1 row ");
			
			db::store($type,$data,array('LICNUM'),true);
			
			$timer->checkpoint("Saved 1 row ");
				

		}
		//$timer->out();
		//R::freeze( true );
	}
	

}

$r= new cslb_ca_gov();
$r->parseCommandLine();

