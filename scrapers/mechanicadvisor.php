<?
include_once "config.inc";

class mechanicadvisor extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		

		// R::freeze();
		$type = get_class();		
		//db::query("DELETE FROM load_queue  where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		$this->threads=4;

		$this->debug=false;
		//$this->proxy = "localhost:9666";

		//$this->loadUrlsByZip("http://www.mechanicadvisor-usa.com/_en/_us/conso/salons/salon_result.aspx?zipcode=%ZIP%&SearchFilter=&ispostback=1",0);

		#$this->loadUrlsByZip("http://www.mechanicadvisor.com/shops.aspx?&l=%ZIP%&miles=20.25&premiumMiles=20.25&IdQuery=25990");
		

		$url = "http://www.mechanicadvisor.com/auto-repair-shops.aspx";
/*
		INSERT  INTO demandforce.load_queue (url,type,processing) SELECT url,'mechanicadvisor',1 FROM jordan.raw_data r WHERE r.type = 'mechanicadvisor' or r.type = 'mechanicadvisor2' 

		INSERT  INTO demandforce.raw_data (url,type,html) SELECT url,'mechanicadvisor',html FROM jordan.raw_data r WHERE r.type = 'mechanicadvisor' or r.type = 'mechanicadvisor2' 
			*/

/*
		db::query("		
		UPDATE load_queue
		 
		 SET processing = 1

		 WHERE
			 url IN (SELECT url FROM raw_data WHERE type ='$type')
			 AND
			 type ='$type'
		");
		*/
		
		
   }

	static function hrefHelper($token, $x, $url)
	{
		$found=false;
		foreach ($x->query("//a[contains(@id,'$token')]") as $node)
		{
			
			$href = self::relative2absolute($url,$node->getAttribute("href"));
			self::getInstance()->loadUrl($href); 
			log::info($node->textContent . " ($token) => $href ");
			$found=true;
		} 
		
		return $found;
	}



	static function parse($url,$html)
	{
		$type = get_class();		
		
		$html = preg_replace("/<br.?\/>/","|",$html);
		$html = preg_replace("/<br>/","|",$html);
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		
		$domTop = new DOMDocument();
		@$domTop->loadHTML($html);
		$xTop = new DOMXPath($domTop);	

		self::hrefHelper('_lnkNameShop',$xTop,$url);
		self::hrefHelper('_lnkService',$xTop,$url);
		
		// first see if this is a listing page, if so grab all the links

		// everything is under shops details

		foreach ($xTop->query("//div[@id='shopsDetails']") as $nodeTop)
		{
			$dom = new DOMDocument();
			@$dom->loadHTML($nodeTop->c14n());
			$x = new DOMXPath($dom);

			$data = array();

			foreach ($x->query("//span[contains(@id,'_txtCompanyName')]") as $node)
			{
				$data['name'] = $node->textContent;
			}

			foreach ($x->query("//p[contains(@id,'_lblAddress')]") as $node)
			{
				$info = explode("|", trim($node->textContent));
				$data['address'] = $info[0];				
				$data['phone'] = $info[2];

				preg_match("/(.+), ([A-Z][A-Z]) ([0-9]{5})/", $info[1],$matches);
				$data['city'] = $matches[1];
				$data['state'] = $matches[2];
				$data['zip'] = $matches[3];

				//$data['full_location'] = $node->textContent;
			}
			
			foreach ($x->query("//a[contains(@id,'_lnkWebsite')]") as $node)
			{
				$data['url'] = self::getFinalAddress($node->getAttribute("href"));
			}


			foreach ($x->query("//span[@class='value-title']") as $node)
			{
				$data['star_rating'] = $node->getAttribute("title");
			}

			
			$times= array();
			foreach ($x->query("//span[contains(@id,'_lblListTimes')]/p") as $node)
			{
				$times[] = $node->textContent;
			}
			if (sizeof($times))
			{
				$days = array(0=>"Sun",  1=>"Mon", 2=>"Tue", 3=>"Wed", 4=>"Thu", 5 => "Fri", 6 =>"Sat");
				for($i=0;$i<7;$i++)
				{
					$data[$days[$i]] = $times[$i];
				}
			}		

			// now do services		
			foreach ($x->query("//span[contains(@id,'_lblServices')]//a") as $node)
			{
				$key = str_replace(" ","_",trim($node->textContent));
				$data[$key] = "Yes";
			
			}
			
			if (isset($data['name']) && isset($data['phone']))
			{
				print_r($data);
				try
				{
					db::store($type, $data,array('name','phone'),true);
				}
				catch (Exception $e)
				{
					log::info($e->getMessage());
				}
			}
		}
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

	static function getFinalAddress( $url ) 
	{ 
		 $options = array( 
			  CURLOPT_RETURNTRANSFER => true,     // return web page 
			  CURLOPT_HEADER         => true,    // return headers 
			  CURLOPT_FOLLOWLOCATION => true,     // follow redirects 
			  CURLOPT_ENCODING       => "",       // handle all encodings 
			  CURLOPT_USERAGENT      => "spider", // who am i 
			  CURLOPT_AUTOREFERER    => true,     // set referer on redirect 
			  CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect 
			  CURLOPT_TIMEOUT        => 120,      // timeout on response 
			  CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects 
		 ); 

		 $ch      = curl_init( $url ); 
		 curl_setopt_array( $ch, $options ); 
		 $content = curl_exec( $ch ); 
		 $err     = curl_errno( $ch ); 
		 $errmsg  = curl_error( $ch ); 
		 $header  = curl_getinfo( $ch ); 
		 curl_close( $ch ); 

		 //$header['errno']   = $err; 
		// $header['errmsg']  = $errmsg; 
		 //$header['content'] = $content; 
		 return $header["url"]; 
	}  

}
global $nomatch;
$nomatch = array();
$r = new mechanicadvisor();
$r->generateCSV();
$r->saveZip('C:\dev\htdocs\demandforce\auto');

//for($i=0;$i<5;$i++)
{
	$r->runLoader();
	$r->parseData();
}

$r->generateCSV();
$r->saveZip('C:\dev\htdocs\demandforce\auto');