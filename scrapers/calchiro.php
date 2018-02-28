<?
include_once "config.inc";

class calchiro extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' ");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		#db::query("DELETE FROM $type");

      // because the static callback needs to call our parse data function.
      //db::query("DELETE FROM load_queue where type='icoi' ");//and url like '%Next%'");


      $this->loadUrlsByStateZip("https://members.calchiro.org/cvweb_calchiro/cgi-bin/memberdll.dll/CustomList?DIST=5&CUSTZIP=%ZIP%&submit.x=22&submit.y=11&submit=Search&WHP=doctor_header.html&WBP=doctor_list.html&SORT=LASTNAME&CUSTOMERTYPE=%7CIN%7CFULL%2CPT%2CLIFE%2CDIST%2C1ST6MOS%2C2ND6MOS%2CYR2%2CYR3%2CFMLY%2CSRAC%2CWPP&SQLNAME=ZIPDIST&ISMEMBERFLG=Y&c.STATUSSTT=Active&CUSTOMERALTCD=&RANGE=1%2F10",'CA');
      
      //$this->proxy = 'localhost:8888';
      $this->threads=1;
		#log::$errorLevel = ERROR_ALL;
      $this->useCookies=true;
      $this->debug=false;
      $this->allowRedirects = true;
   }

	static function parse($url,$html)
	{
		$data=array();
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	
		
		// check for next link.
		foreach( $x->query("//a[@id='nextlnk']") as $node)
		{
			$nextUrl = "https://members.calchiro.org/cvweb_calchiro/cgi-bin/memberdll.dll/".$node->getAttribute("href");
			log::info("Paging $nextUrl");
			self::getInstance()->loadUrl($nextUrl);
			break;
		}


		foreach ($x->query("//div[@class='info']//table//tr") as $listing)
		{
			$dom2 = new DOMDocument();
			@$dom2->loadHTML($listing->c14n());
			$x2 = new DOMXPath($dom2);	
			
			$i=0;
			$data = array();
			foreach($x2->query("//th") as $node)
			{
				$key[$i] = str_replacE(" ","_",trim($node->textContent));
				$i++;
			}
			if ($i>0) continue;
			$i=0;
			foreach($x2->query("//td") as $node)
			{
				if ($i > 7) break;	
				
				$keyName= $key[$i];
				$data[$keyName] = $node->textContent;
				if (preg_match("/else \{document\.write\('(.+)'\)\}/",$node->textContent,$matches))
				{
					$data[$keyName] = $matches[1];
				}
				$i++;
			}
			
			if (!empty($data['First_Name']))
			{
				log::info($data);
				db::store($type, $data,array('First_Name','Phone_Number'),false);
			}
		}
	}
}
$r = new calchiro();
$r->runLoader();
$r->parseData();
$r->generateCSV();
