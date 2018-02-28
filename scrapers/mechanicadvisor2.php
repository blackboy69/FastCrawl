<?
include_once "config.inc";


class mechanicadvisor2 extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		

		// R::freeze();
		$type = get_class();		
		//db::query("UPDATE  load_queue set processing = 0 where type='$type'");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		 //db::query("delete from load_queue	where type='$type' ");
		 //db::query("delete from raw_data		where type='$type' ");
		 //db::query("delete from $type ");

		$this->threads=6;

	//	log::$errorLevel = ERROR_ALL;
//		$this->debug=true;

		//$this->loadUrlsByZip("http://www.mechanicadvisor2-usa.com/_en/_us/conso/salons/salon_result.aspx?zipcode=%ZIP%&11:25 AMSearchFilter=&ispostback=1",0);

		#$this->loadUrlsByZip("http://www.mechanicadvisor2.com/shops.aspx?&l=%ZIP%&miles=20.25&premiumMiles=20.25&IdQuery=25990");
		

		$url = "http://www.mechanicadvisor.com/auto-repair-shops.aspx";

		$this->loadUrl($url);

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


		$this->queuedGet();
		
   }

	static function hrefHelper($token, $x, $url)
	{
		$found=false;
		foreach ($x->query("//*[contains(@id,'$token')]") as $node)
		{
			log::info($node->textContent);
			$href = self::relative2absolute($url,$node->getAttribute("href"));
			self::getInstance()->loadUrl($href); 
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

		// first see if this is a listing page, if so grab all the links

		// everything is under shops details

		foreach ($xTop->query("//div[@id='shopsDetails']") as $nodeTop)
		{
			$dom = new DOMDocument();
			@$dom->loadHTML($nodeTop->c14n());
			$x = new DOMXPath($dom);

			if (self::hrefHelper('_lnkState',	$x,$url)) return;
			if (self::hrefHelper('_lnkCity',		$x,$url)) return;


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

			print_r($data);
			db::store($type, $data,array('name','phone'),false);
		}

		if (self::hrefHelper('_lnkService',	$x,$url)) return;
		if (self::hrefHelper('_lnkNameShop',$x,$url)) return;

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
global $nomatch;
$nomatch = array();
$r = new mechanicadvisor2();

//for($i=0;$i<5;$i++)
{
	$r->runLoader();
	$r->parseData();
}

$r->generateCSV();