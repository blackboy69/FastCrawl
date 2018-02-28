<?
include_once "config.inc";

class mystlukesonline extends baseScrape
{
    public static $_this=null;
	public static $google = null;
	public static $bing = null;	
	public static $csv=null;
	public static $csvMap=array();

	private $urlsToLoad = array();
   public function runLoader()
   {
		
	//	$type = get_class();		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
//db::query("DROP TABLE $type");	
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

		for($i = 1;$i<2200;$i++)
		{
			$url[] = "http://www.mystlukesonline.org/find-doctor/doctor-detail.aspx?doctorID=$i&sid=1486887&sort=3&pg=1";
		}
		$this->loadUrlsByArray($url);
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


		$x = new Xpath($html);
		foreach($x->query("//*[contains(@id,'DoctorName')]") as $node)
		{
			$data['Name'] = self::cleanup($node->textContent);
		}

		if (isset($data['Name']))
		{	

			foreach($x->query("//*[contains(@id,'DoctorTitles')]") as $node)
			{
				$data['Titles'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//*[contains(@id,'Phone')]") as $node)
			{
				list($key,$value) = explode(":", $node->textContent);
				$data[self::cleanup($key)] = self::cleanup($value);
			}
			foreach($x->query("//*[contains(@id,'Fax')]") as $node)
			{
				@list($key,$value) = explode(":", $node->textContent);
				$data[self::cleanup($key)] = self::cleanup($value);
			}

			foreach($x->query("//*[contains(@for,'SpecialtyList')]/following-sibling::*") as $node)
			{
				$data["Specialties"] = self::cleanup($node->textContent);
			}

			foreach($x->query("//*[contains(@for,'Gender')]/following-sibling::text()") as $node)
			{
				$data["Gender"] = self::cleanup($node->textContent);
			}


			$c_and_s = "";
			foreach($x->query("//a[contains(@href,'/conditions-and-services/')]") as $node)
			{
				$c_and_s = self::cleanup($node->textContent);
			}
			$data['Clinical Expertise']= $c_and_s;


			foreach($x->query("//div[contains(@id,'facilities')]") as $node)
			{
				$x2 = new Xpath($node);
				$practice = "";
				foreach($x2->query("//practicename") as $node2)
				{
					$practice = self::cleanup($node2->textContent);
				}

				if (! empty($practice))
				{
					$i=0;					
					$data["Practice $i"] = $practice;
					foreach($x2->query("//a[contains(@href,'google')]") as $node2)
					{
						$mapQuery = $node2->getAttribute("href");
						parse_str(parse_url($mapQuery,PHP_URL_QUERY),$mq); // address and zip	
						
						foreach($ap->parse($mq['daddr']) as $k=>$v)
						{
							$data["$k $i"] = $v;
						}
						$i++;
					}
				}
			}		

			try {				
				 $data['Source Url'] = $url;
				 $data['doctorID'] = $query['doctorID'];
				log::info($data);

				 db::store($type,$data,array('doctorID'));
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
			log::Error("Not Found $url");
		}
	}

		static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/"," ",$str);
	}
	
	static function cleanup($str)
	{
		while (preg_match("/  /",$str))
		{
			$str = preg_replace("/  /i"," ",$str);
		}
		return trim(self::removeSpaces($str));
	}
}
$r = new mystlukesonline();
$r->parseCommandLine();
