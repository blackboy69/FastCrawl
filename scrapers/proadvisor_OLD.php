<?
include_once "config.inc";

class proadvisor extends baseScrape
{
	
   public function runLoader()
   {
		
		$type = get_class();		

		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");
*/

		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		

		$this->proxy = "localhost:8888";

		$this->noProxy=false;
		
		$this->debug=false;
		$this->threads=1;
		$this->useCookies = true;
		$this->timeout = 15;

		$this->loadUrlsByZip("http://proadvisor.intuit.com/fap/?zip=%ZIP%",75000);

		$this->queuedGet();

	}

	private static function captchaSearch($zip,$html)
	{		
		$thiz = self::getInstance();	
		$cp = new captcha_parser();

		$fp = new HtmlParser($html);
		$form['zipCode'] = $zip;
		$form['distance'] = 500;
		$form = array_merge($form, $fp->getForm('MemberSearch',false));
		$response =  $this->Post("http://proadvisor.intuit.com/fap/fap/fap_results.jsp?_DARGS=/_intuit/proadvisor/fap/find_a_proadvisor.jsp&".$this->buildQuery($form,2));		

		if (preg_match("/Automated programs known as \"Bots\" can get here and pretend they are doing a search similar to yours/",$response))
		{
			// get search parameters
			$searchPage = new HtmlParser($thiz->Get("http://proadvisor.intuit.com/fap/fap_captcha.jsp?_DARGS=/_intuit/proadvisor/fap/fap_results.jsp"));
			$searchForm = $searchPage->getForm();

			// load captcha page and captcha
			$captcha_image_data = $thiz->Get("http://proadvisor.intuit.com/fap/fragments/fap_captcha_image.jsp");
			$captcha_image_filename=tempnam("","jpg");
			file_put_contents($captcha_image_filename, $captcha_image_data);
			$captcha_text = $cp->parse($captcha_image_filename);
			unlink($captcha_image_filename);		

			// verify captcha
			$response = $thiz->Post("http://proadvisor.intuit.com/fap/fap_results.jsp?_DARGS=/_intuit/proadvisor/fap/fap_captcha.jsp?", $form);	

			if (preg_match("/The characters you entered did not match/",$response)
			{
				log::info("FAILED DECODE:   $captcha_text");
				return self::captchaSearch($zip, $html);
			}
			log::info("CAPTCHA DECODED: $captcha_text");
		}
		return $response;
	}

	public static function loadCallBack($url,$html,$type)
	{
		if (preg_match("/proadvisor.intuit\.com\/fap\//",$url))
		{
			parse_str(parse_url($url,PHP_URL_QUERY),$query); 
			self::captchaSearch($query['zip'],$html);
		}
		parent::loadCallBack($url,$html,$type);
	}

	private function parseProfile($url,$html)
	{
		$query = array();
		$type = get_class();		
		$thiz = self::getInstance();		
		$host = parse_url($url,PHP_URL_HOST);
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip			

		log::error("parse profile not implemented");
		exit;
	}

	private function parseListings($url,$html)
	{
		$query = array();
		$type = get_class();		
		$thiz = self::getInstance();		
		$host = parse_url($url,PHP_URL_HOST);
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip			

		$response = self::captchaSearch($query['zip'],$html);

		$pageCount = 1;
		$pageSize = 1000;
		for($i=1;$i<$pageCount;$i++)
		{
			$results = json_decode($thiz->Get("http://proadvisor.intuit.com/estore/apd/acm/SearchResults?pageNumber=$i&pageSize=$pageSize&sort=&pageCount=$pageCount "),true);
		
			$searchResults = $results['searchResults'];
			$pageCount = $results['pageCount'];
			$pageSize = $results['pageSize'];
			$rowCount = $results['rowCount'];
			
			$urls = array();
			foreach($searchResults as $data)
			{
				$id =  $data['encryptedId'];
				$urls[] = "http://proadvisor.intuit.com/referral/proadvisor_profile.jsp?id=".urlencode($id);
				echo ".";
				try {				
					db::store($type,$data,array('encryptedId'));
				}
				catch(Exception $e)
				{
					log::error ("Cannot store ".$data['Name']);
					log::error($e);
					//print_r($data);
					exit;
				}	
			}
			$this->loadUrlsByArray($urls);
		}
	}
}
$r = new proadvisor();
$r->parseCommandLine();
