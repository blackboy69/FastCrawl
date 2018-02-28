<?
include_once "config.inc";

class scmc extends baseScrape
{
    public static $_this=null;
	public static $google = null;
	public static $bing = null;	
	public static $csv=null;
	public static $csvMap=array();

	private $urlsToLoad = array();
   public function runLoader()
   {
		
	$type = get_class();		
	db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
	db::query("DROP TABLE $type");	

		/*	db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
			//db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
		

		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");

		db::query("DROP TABLE $type");	
		
*/
		
		//$this->proxy = "localhost:8888";		
		$this->noProxy=true;

		// $this->threads=1;
		// $this->useCookies = true;
		// $this->timeout = 8;

		$url = "http://physicianfinder.scmc.org/searchResults";
		$this->loadUrl($url);
		$this->queuedGet();
	}
	
	static function parse($url,$html)
	{
		$t=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$urls = array();

		$data = array();
		if (preg_match("/searchResults/",$url))
		{
			$urls = array();
			$x = new Xpath($html);
			foreach($x->query("//a[contains(@href,'viewDoctor?id=')]") as $node)
			{
				$urls[] = self::relative2absolute($url, $node->getAttribute('href'));
			}
			self::getInstance()->loadUrlsByArray($urls);
		}
		else if (preg_match("/viewDoctor/",$url))
		{
			$data['xid']= $query['id'];
			

			$x = new Xpath($html);
			foreach($x->query("//*[contains(@class,'viewDoctorName')]") as $node)
			{
				$data['Name'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//*[contains(@class,'viewDoctorSpecialty')]") as $node)
			{
				$data['Specialty'] = self::cleanup($node->textContent);
			}

			$i=1;
			foreach($x->query("//div[contains(@class,'viewDoctorOfficeHeader')]") as $node)
			{
				
				$x2 = new Xpath($node);
				foreach($x2->query("//div[contains(@class,'viewDoctorOfficeName')]") as $node2)
				{
					$data['Office Name '.$i] = self::cleanup($node2->textContent);
				}

				foreach($x2->query("//div[contains(@class,'viewDoctorOfficePhone')]") as $node2)
				{
					list($junk, $data['Office Phone '.$i]) = explode(':', self::cleanup($node2->textContent));
				}
				
				$address = array();
				foreach($x2->query("//div[not(@class)]") as $node2)
				{
					$address[] = self::cleanup($node2->textContent);
				}
				
				foreach($ap->parse($address) as $k=>$v)
				{
					$data["$k $i"] = $v;
				}
				$i++;
			}

			$certs = array();
			foreach($x->query("//*[contains(@class,'viewDoctorOfficeCertificationsHeader')]") as $node)
			{	
				$x2 = new Xpath($node);
				$label = "";

				foreach($x2->query("//div[contains(@class,'viewDoctorOfficeCertsItem')]") as $node2)
				{
					$label=self::cleanup($node2->textContent);
				}
				
				$rollup=array();
				foreach($x2->query("//div[not(@class)] | //div[@class='viewDoctorEducationWidth']") as $node2)
				{
					$rollup[] = self::cleanup($node2->textContent);
				}

				if (! empty($label))
					$data[$label] = join(", ",$rollup);
			}

			try {				
				 $data['Source Url'] = $url;
				 $data['xid'] = $query['id'];
				log::info($data);

				db::store($type,$data,array('xid'));
			}
			catch(Exception $e)
			{
				log::error ("Cannot store ".$data['Name']);
				log::error($e);
				//print_r($data);
				exit;
			}		
		}
		else
		{
			log::error("Cannot parse $url");
		}
	}


}
$r = new scmc();
$r->parseCommandLine();
