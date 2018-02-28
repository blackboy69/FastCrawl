<?
include_once "config.inc";

class abda extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		db::query("DELETE FROM load_queue where type='$type' ");
		#db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		db::query("DELETE FROM raw_data where type='$type' ");
		#db::query("DELETE FROM $type");

		
		$this->threads=4;

		$this->debug=false;
		//log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";
		
//		unlink ($this->cookie_file);
	//	$this->useCookies=true;
		//$this->login();
		//$this->loadUrl("https://www.abda.org/member/findadentist_results.aspx?fragmentid=166&lastname=&city=&withinmiles=0&zipcode=01230&practicetype=AllSpecialties&foreignlanguage=AllLanguages");
		
	/*	for($i=0 ; $i<=80 ; $i+=10)
		{
			$this->loadUrlsByStateZip("http://www.abda.ab.ca/dentist_locator_results_dnn.asp?DentistType=$i&PostalCode=%ZIP%&City=&Submit=Search",'AB');
		}
		*/
		
		// after a few runs I found these are the only search parameters that return values. this speeds up the search by at least 10x
		$peices = array ('DentistType=0&PostalCode=T0A', 'DentistType=0&PostalCode=T0B', 'DentistType=0&PostalCode=T0C', 'DentistType=0&PostalCode=T0E', 'DentistType=0&PostalCode=T0G', 'DentistType=0&PostalCode=T0H', 'DentistType=0&PostalCode=T0J', 'DentistType=0&PostalCode=T0K', 'DentistType=0&PostalCode=T0L', 'DentistType=0&PostalCode=T0M', 'DentistType=0&PostalCode=T1A', 'DentistType=0&PostalCode=T1K', 'DentistType=0&PostalCode=T3K', 'DentistType=0&PostalCode=T4N', 'DentistType=0&PostalCode=T4S', 'DentistType=0&PostalCode=T4V', 'DentistType=0&PostalCode=T5T', 'DentistType=0&PostalCode=T8A', 'DentistType=0&PostalCode=T8N', 'DentistType=0&PostalCode=T8R', 'DentistType=0&PostalCode=T8V', 'DentistType=0&PostalCode=T9A', 'DentistType=0&PostalCode=T9M', 'DentistType=0&PostalCode=T9S', 'DentistType=0&PostalCode=T9V', 'DentistType=20&PostalCode=T4N', 'DentistType=20&PostalCode=T5T', 'DentistType=40&PostalCode=T4N', 'DentistType=40&PostalCode=T5T', 'DentistType=40&PostalCode=T8A', 'DentistType=50&PostalCode=T3K', 'DentistType=50&PostalCode=T4N', 'DentistType=50&PostalCode=T5T', 'DentistType=50&PostalCode=T8A', 'DentistType=50&PostalCode=T8N', 'DentistType=50&PostalCode=T8R', 'DentistType=50&PostalCode=T8V', 'DentistType=50&PostalCode=T9M', 'DentistType=60&PostalCode=T3K', 'DentistType=60&PostalCode=T4N', 'DentistType=70&PostalCode=T5T');

		foreach($peices as $piece)
		{
			$this->loadUrl("http://www.abda.ab.ca/dentist_locator_results_dnn.asp?$piece&City=&Submit=Search");
		}
		$this->queuedPost();

   }

   public static function loadCallBack($url,$html,$type)
   {
		if (preg_match("/available=EOF|WPC/",$url))
		{
			log::info("Skipping $url");
		}
	}
	
	static function parse($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		
		$i=0;
		foreach ($x->query("//td") as $node)
		{
			if ($i++ <= 4)
				continue;

			$doc =  preg_replace("/<br>/", " *---* ",$node->c14n());
			
			$data = array();
			$extra = array();

			$lines = explode("*---*",strip_tags($doc));
			$j=0;
			foreach($lines as $line)
			{
				log::info($line);
				if (preg_match ("/Click here for map/", $line))
				{
					continue;
				}

				switch ($j++)
				{
					case 0:
						break;

					case 1:
						$data['name'] = trim($line);
						break;

					case 2:
						// if this doesn't look like a practice name, then skip.
						if (!preg_match("/[0-9].+[0-9]/", trim($line)))
						{
							$data['practice'] = $line;
							break;
						}
						else
						{
							$j++; // increment counter so next line doesn't break;
						}
						// otherwise fallthrough to the next line

					case 3:
						$data['street_address'] = trim($line);
						break;

					default :
		
						if (preg_match("/[ ]+AB[ ]+T../", trim($line))) 
						{
							$data['citystatezip'] = $line;
						}
						else if (preg_match("/\([0-9]{3}\) [0-9]{3}-[0-9]{4}/i",$line))
						{
							$data['phone'] = preg_replace("/Phone:/i", "", trim($line));
						}
						else
						{
							$extra[] = $line;
						}
						
				}
			}

			if (sizeof($extra)>0)
			{
				$data['extra'] = join(", ", $extra);
			}
			
			log::info( $data );
         db::replaceInto($type,$data);
		}

		log::info ("Done parsing");

	}


	static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/","",$str);
	}
	
	static function cleanup($str)
	{
		while (preg_match("/  /",$str))
		{
			$str = preg_replace("/  /i"," ",$str);
		}
		return trim($str);
	}

}
$r = new abda();

for($i=0;$i<5;$i++)
{
	$r->runLoader();
	$r->parseData();
}

$r->generateCSV();
