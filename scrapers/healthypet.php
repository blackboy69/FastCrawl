<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class healthypet extends baseScrape
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


		$this->loadUrlsByZip('http://www.healthypet.com/Accreditation/HospSearchResults.aspx?country=US&radius=500&postalCode=%ZIP%');
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	

		if (strpos($url, "HospSearchResults.aspx") > 0)
		{
			/// get next page links
			$urls = array();
			foreach($x->query("//a[contains(@href,'HospitalDetail.aspx')]") as $node)
			{
				$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
			}
			if (!empty($urls))
			$thiz->loadUrlsByArray($urls);				
		}

		if (strpos($url, "HospitalDetail.aspx") > 0)
		{
			
			$ap = new Address_Parser();
			$op = new Operating_Hours_Parser();
			$pp = new Phone_Parser();
			$kvp = new KeyValue_Parser();


			$data = array();

			foreach ($x->query("//*[@id='ctl00_ContentPlaceHolder1_hTitle']") as $node)
			{
				$data['NAME'] = $node->textContent;
			}

			foreach ($x->query("//div[@id='ctl00_ContentPlaceHolder1_divAddress']//div") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
				break;
			}

			foreach ($x->query("//div[@class='padTopBot10']") as $node)
			{
				$data = array_merge($data,$kvp->parse($node->c14n()));
			}
			foreach ($x->query("//*[@id='ctl00_ContentPlaceHolder1_divJoinDate']") as $node)
			{
				$data['ACCREDITATION'] = $node->textContent;
			}

			$vets = array();
			foreach ($x->query("//*[@id='ctl00_ContentPlaceHolder1_tdVets']//li[@class='bullet']") as $node)
			{
				$vets[] = trim($node->textContent);
			}
			$data['VETS']= join("|",$vets);

			$species = array();
			foreach ($x->query("//*[@id='ctl00_ContentPlaceHolder1_tdSpecies']//li[@class='bullet']") as $node)
			{
				$species[] = trim($node->textContent);
			}
			$data['SPECIES']= join("|",$species);

			if (!empty($data))
			{
				$data['SOURCE_URL'] = $url;
				log::info($data);		
				db::store($type,$data,array('SOURCE_URL'));	
			}		
			
		}
	}
}

$r= new healthypet();
$r->parseCommandLine();

