<?
include_once "config.inc";
include_once "captcha_parser_noise.php";

class car_org2 extends baseScrape
{
   public function runLoader()
   {
      $type = get_class();    

      $this->maxRetries = 25; // switch to a different url if it returns no data> this many times
      $this->timeout = 10;
      $this->useCookies=true;
      $this->allowRedirects = true;
      $this->debug=false;
		$this->threads=1;
		$this->noProxy=false;
		//$this->proxy = 'localhost:9666';
		//$this->switchProxy(null,true);
		$this->needCaptcha=true;
		
//		db::query("update load_queue where  type='$type' and url like 'http://www.car.org/CAR_UMS/search.do%");
/*		db::query("delete from load_queue where type='$type' and  url in (select url from raw_data where type='$type' and html like '%You are not verified for the search.%')");
		db::query("delete from raw_data where type='$type' and  html like '%You are not verified for the search.%'");
	
		db::query("delete from raw_data where type='$type' and  html like '%You are not verified for the search.%'");
		db::query("delete from load_queue where type='$type' and  url NOT in (select url from raw_data where type='$type)"); */	
/*
db::query("DELETE FROM raw_data where type='$type' url not like 'http://www.car.org/CAR_UMS/webpage.do%'");
db::query("DELETE FROM load_queue where  type='$type' and url not like 'http://www.car.org/CAR_UMS/webpage.do%'");



db::query("delete from load_queue where type='$type' and  url in (select url from raw_data where type='$type' and html like '%You are not verified for the search.%')");
db::query("update raw_data  set parsed = 1 where type='$type' and html like '%You are not verified for the search.%'");
db::query("update raw_data set parsed = 1 where type='$type' and parsed = 0 and url like 'http://www.car.org/CAR_UMS/search.do%'");
*/
		//db::query("delete from load_queue where  type='$type' and url like 'http://www.car.org/CAR_UMS/webpage.do%'");
//		db::query("UPDATE  load_queue set processing=0 where type='$type' and processing=1 and url not in (select url from raw_data where type='$type' and html not like '%You are not verified for the search.%')  ");

		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1 and url like 'http://www.car.org/CAR_UMS/webpage.do%' ");
		//db::query("UPDATE  raw_data set parsed=0 where type='$type' and parsed=1 ");
//		db::query("DROP TABLE $type");	

		///				SELECT zip FROM geo.locations where zip > 04500 
	  $result = mysql_query("SELECT 
				distinct geo.locations.zip  as ZIP 
			FROM  geo.locations 			
			WHERE state = 'CA'
			and zip > 91767
			order by zip 
		");

		while ($r = mysql_fetch_row($result))
      {
			$zip = sprintf("%05d", $r[0]);	
		log::info("Loading $zip");

//			continue;
			do 
			{
				$this->breakCaptcha();						
				$urlToPost = "http://www.car.org/CAR_UMS/search.do?zip=$zip";		
				$webRequests[] = new WebRequest($urlToPost,$type, "POST", "zipCity=$zip&distance=0+Miles&x=".rand(0,46)."&y=".rand(0,13)."&lastName=&firstName=&office=&captchaKey=$this->captchaKey&searchType=member"); 
				$this->loadWebRequests($webRequests);				
				//serialize this one.
				$this->parseData();
				$this->parseData();
				$this->parseData();

			} while ($this->needCaptcha);

      }
	}

	function breakCaptcha()
	{
		while ($this->needCaptcha)
		{
			$cpn = new Captcha_Parser_Noise();
			$url = "http://www.car.org/CAR_UMS/loadSearchScreen.do?searchType=member&siteid=1";
			$html = $this->get($url);
			$x = new Xpath($html);
			foreach($x->query("//img[contains(@src,'Captcha.jpg?')]") as $node)
			{
				$href = self::relative2absolute($url, $node->getAttribute("src"));
				break;
			}
	
			$imgContents = $this->get($href);

			log::info($href);
			$imgName =  tempnam(sys_get_temp_dir(), 'JPG') . ".JPG"	;
			file_put_contents($imgName,$imgContents);
			$this->captchaKey = $cpn->parse($imgName);
			unlink($imgName);

			log::info("Got captchaKey $this->captchaKey");

			$urlToPost = "http://www.car.org/CAR_UMS/search.do";		
			$html = $this->Post($urlToPost, "zipCity=Windsor,CA&distance=0+Miles&x=".rand(0,46)."&y=".rand(0,13)."&lastName=&firstName=&office=&captchaKey=$this->captchaKey&searchType=member");    

			$this->checkNeedCaptcha($url,$html);
		}
	}
	
	function checkNeedCaptcha($url, $html)
	{
		if (strpos($html,"You are not verified for the search.") > -1)
		{
			log::info("Failed. - You are not verified for the search.");
			$this->needCaptcha = true;
		}
		else if ($url == 'http://www.car.org/')
		{
			log::info("Failed. - redirected to home page!");
			$this->needCaptcha = true;
		}
		else
		{
			log::info("Captcha broken!");
			$this->needCaptcha = false;
		}
		return $this->needCaptcha;
	}

	// break the captcha
   static function loadCallBack($url,$html,$arg3)
   {
		log::info("Loaded: $url");
      if (empty($url)) //timeout?
         return;

      $thiz = self::getInstance();

		$thiz->checkNeedCaptcha($url,$html);
		return baseScrape::loadCallBack($url,$html,$arg3);		

   }



	function parse($url,$html)
	{		
		log::info($url);
		$x = new Xpath($html);
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip			

		if (strpos($url,"/CAR_UMS/search.do") > -1)
		{
		
			$urls = array();
			foreach($x->query("//a[contains(@onclick,'viewProfile')]") as $node)
			{
				// javascript:viewProfile('202500875')
				$onclick = $node->getAttribute("onclick");

				$carid = preg_replace("/[^0-9]+/","",$onclick);			

				$urls[] = new WebRequest("http://www.car.org/CAR_UMS/webpage.do?carid=$carid",$type, "POST", "carid=$carid&searchType=member");	

			}

			if (sizeof($urls) ==20)
			{
				//debug
				//$urls=array();


				$zip = $query['zip'];
				if (! isset($query['page']))
					$nextPage = 2;
				else 
					$nextPage = $query['page'] + 1;

				$urlToPost = "http://www.car.org/CAR_UMS/search.do?zip=$zip&page=$nextPage";		
				$urls[] = new WebRequest($urlToPost,$type, "POST", "zipCity=$zip&distance=0+Miles&lastName=&firstName=&office=&captchaKey=$thiz->captchaKey&searchType=member&pageNumber=$nextPage"); 	

			}		

			log::info("Loading ".sizeof($urls). "Urls");
			$thiz->loadWebRequests($urls);
		}
		else
		{
			$ap = new Address_Parser();
			$pp = new phone_parser();
			$ep = new Email_Parser();
			$kvp = new KeyValue_Parser();	

			$data = array();
			foreach($x->query("//div[contains(@class,'panel_header')]/div[1]") as $node)
			{
				$data['NAME'] = self::cleanup($node->textContent);
			}


			foreach($x->query("//table[contains(@class,'typ_gray11')]//tr") as $node)
			{
				$data = array_merge($data, $kvp->parse(self::cleanup($node->textContent)));
			}



			if (isset($data['NAME']))
			{
				$data = array_merge($data, $ap->parse($data['OFFICE']));
				$data['SOURCE_URL'] = $url;
				log::info($data);
				db::store($type,$data,array('SOURCE_URL'));
			}
		}
	}
}

$r= new car_org2();
$r->parseCommandLine();

