<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class intuit_contractors extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		

//		$this->maxRetries = 100;
	//	$this->timeout = 15;
		//$this->useCookies=false;
		$this->allowRedirects = true;
		$this->debug=false;
		$this->threads=8;
//	$this->debug=true;
	//log::error_level(ERROR_DEBUG_VERBOSE);
		
		/*
	   db::query("
		delete from load_queue
		 
		 WHERE			 
			 (
			 url IN (SELECT url FROM raw_data WHERE type ='intuit_contractors' and LENGTH(html) < 3000)
			 AND type ='intuit_contractors'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='intuit_contractors'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='intuit_contractors')
				  AND processing = 0
			     AND type ='intuit_contractors'
		    )
		
			 ");
		
		*/
		// cananda top 100 cities by population
		db::query("UPDATE raw_data set parsed = 1 where type='intuit_contractors' and parsed = 0  ");
		db::query("drop table $type");
		
	//	$this->noProxy=true;
//		$this->switchProxy(null,true);
		
		// should be 13550 listings
//		$this->loadUrl("http://business.intuit.com/directory/g28476-new-york-ny/c36-contractors-construction");	
		$this->loadUrl("http://listings.homestead.com/info-romeo-juliette-laser-hair-new-york-ny",true);	
	}


	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();

		$urls = array();
		foreach($x->query('//div[@id="mainCenter"]/div/div/h2/a') as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		// Load next page links
		foreach($x->query('//div[@id="searchFooter"]//a') as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		if (!empty($urls))
			$thiz->loadUrlsByArray($urls);	
		//else
		{
			// parse listings...

				$data = array();
			foreach($x->query("//div[@id='businessDetail']//h1") as $node)
			{
				$data['COMPANY'] = self::cleanup($node->textContent);
			}
			if (empty($data['COMPANY'])) return;

			foreach($x->query("//div[@id='phone']") as $node)
			{
				$data['PHONE'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//div[@id='address']") as $node)
			{
				$data = array_merge($data, $ap->parse( $node->c14n() ) );
			}

			foreach($x->query("//div[@id='website']") as $node)
			{
				$data['WEBSITE'] = self::cleanup($node->textContent);
			}

			$rating = 5;
			foreach($x->query("//div[@id='ratingReview']/span[@id='ratings']/span[contains(@class, 'off')]") as $node)
			{
				echo 'WTF';
				print_R($node->textContent);
				$rating--;
			}
			foreach($x->query("//div[@id='ratingReview']/span[@id='ratings']/span[contains(@class, 'half')]") as $node)
			{
				$rating =- .5;
			}
			$data['AVG_RATING'] = $rating;


			foreach($x->query("//a[@id='reviews']") as $node)
			{
				$data['NUM_REVIEWS'] = str_replace("reviews","", self::cleanup($node->getAttribute("content")));
			}

			foreach($x->query("//div[@id='breadcrumbs']/a[1]") as $node)
			{
				$data['CATEGORY'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//div[@id='breadcrumbs']/a[2]") as $node)
			{
				$data['SUB_CATEGORY'] = self::cleanup($node->textContent);
			}

			// Demandforce requirements
			$data['FIRST_NAME']="Not Provided";
			$data['LAST_NAME']="Not Provided";
			if(preg_match("/ |[a-z]/", @$data['ZIP']))
				$data['COUNTRY'] = 'Canada';
			else
				$data['COUNTRY'] = 'United States';

			$data['SOURCE_URL'] = $url;
			log::info($data);
			if (isset($data['SOURCE_URL']))
				db::store($type,$data,array('SOURCE_URL'));	
		
		}

		
		
	}
	
}

$r= new intuit_contractors();
$r->parseCommandLine();

