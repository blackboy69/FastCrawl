<?
include_once "config.inc";
include_once "intuit_homestead_base.php";

class intuit_homestead_dentists extends intuit_homestead_base
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
			 url IN (SELECT url FROM raw_data WHERE type ='intuit_homestead_dentists' and LENGTH(html) < 3000)
			 AND type ='intuit_homestead_dentists'
			 AND processing = 1
			 )
			 OR
			 (
				 processing = 0
			    AND type ='intuit_homestead_dentists'
		    )
			 OR 
			 (
  			     url NOT IN (SELECT url FROM raw_data WHERE type ='intuit_homestead_dentists')
				  AND processing = 0
			     AND type ='intuit_homestead_dentists'
		    )
		
			 ");
		
		*/
		// cananda top 100 cities by population
	//	db::query("UPDATE raw_data set parsed = 0 where type='intuit_homestead_dentists' and parsed = 1  ");
	//	db::query("drop table $type");
		
	//	$this->noProxy=true;
//		$this->switchProxy(null,true);
		
		// should be 13550 listings
		$this->loadUrl("http://business.intuit.com/directory/c135-dentists");	
		//$this->loadUrl("http://listings.homestead.com/info-romeo-juliette-laser-hair-new-york-ny");	
	}
	
	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$op = new Operating_Hours_Parser();

		$urls = array();
		foreach($x->query('//div[@class="searchListing"]//h2/a') as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		foreach($x->query('//div[@id="topCities"]//a') as $node)
		{
			$urls[] = self::relative2absolute($url,$node->getAttribute("href"));
		}
		// Load next page links
		foreach($x->query('//div[@id="searchFooter"]//a') as $node)
		{
			if (strpos("?", $url ) > 1)
				list($href,$junk) =  explode("?",$url);
			else
				$href = $url;

			$urls[] = $href.$node->getAttribute("href");
		}
		print_R($urls);


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

			$rating = 0;
			foreach($x->query("//span[@id='ratings']//span[@class='star']") as $node)
			{
				$rating++;
			}
			foreach($x->query("//span[@id='ratings']//span[@class='star half']") as $node)
			{
				$rating += .5;
			}
			$data['AVG_RATING'] = $rating;


			foreach($x->query("//div[@id='ratingReview']//a") as $node)
			{
				$data['NUM_REVIEWS'] = str_replace("reviews","", self::cleanup($node->textContent));
			}

			foreach($x->query("//div[@id='breadcrumbs']/a[1]") as $node)
			{
				$data['CATEGORY'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//div[@id='breadcrumbs']/a[2]") as $node)
			{
				$data['SUB_CATEGORY'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//div[@id='businessInfo']//label") as $node)
			{
				$k = self::cleanup($node->textContent);
				$v = $node->nextSibling->nextSibling->textContent;

				if (strtolower($k) == 'hours')
					continue;
				$data[$k] = $v;
			}

			$hours=array();
			foreach($x->query("//div[@id='businessInfo']//label[text()='Hours']/following-sibling::span//tr") as $node2)
			{
				$hours[] = $node2->textContent;
			}
			$data = array_merge($data,$op->parse($hours));


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

$r= new intuit_homestead_dentists();
$r->parseCommandLine();

