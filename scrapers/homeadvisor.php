<?
include_once "config.inc";

class homeadvisor extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		
		$type = get_class();		
/*
		db::query("		
			UPDATE load_queue
			 
			 SET processing = 0

			 WHERE			 
				 url IN (SELECT url FROM raw_data WHERE type ='$type' and LENGTH(html) < 3000 And url like 'http://www.google.com%')
				 AND type ='$type'
		");*/

		/*
		db::query("UPDATE load_queue set processing=0 where type='$type' and processing=1");
		db::query("UPDATE  raw_data set parsed=0 where type='$type' ");


		db::query("DROP TABLE $type");	
		
*/
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
	//	db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='homeadvisor' ");
//		db::query("DROP TABLE $type");	

		//$this->noProxy=true;

		//db::query("DELETE FROM load_queue where type='$type'");
	//	db::query("DELETE FROM raw_data where type='$type' ");

		$this->loadUrlsByZip("http://www.homeadvisor.com/c.Cleaning-Maid-Services.Rohnert_Park.CA.-12014.html?findContractor=listingsZipSearch&category_select=-12014&zip=%ZIP%",25000);
		$this->loadUrlsByZip("http://www.homeadvisor.com/c.Lawn-Garden-Care.Rohnert_Park.CA.-12014.html?findContractor=listingsZipSearch&category_select=-12014&zip=%ZIP%",25000);
	}
	
	static function parse($url,$html)
	{
		$thiz=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$np = new Name_Parser();
		$urls = array();

		$data = array();
	
		$xTop = new Xpath($html);

		
		foreach($xTop->query("//a[text() = 'Next >']") as $node)
		{
			$urls[] = self::relative2absolute($url, $node->getAttribute("href")); // next links
		}
		$thiz->loadUrlsByArray($urls);

		$category = "";
		foreach($xTop->query("//select[@name='category_select']/option[@selected]") as $node)
		{
			$category = $node->textContent;
		}



		foreach($xTop->query("//div[contains(@class,'xmd-listing xmd-stacked-box t-utility-box l-columns l-four-columns')]") as $nodeTop)
		{
			$data = array();
			$data['CATEGORIES']= $category;

			$x = new Xpath($nodeTop);

			foreach($x->query("//a[@class='xmd-listing-company-name']") as $node)
			{
				$data['COMPANY'] = self::cleanup($node->textContent);
				$data['SOURCE_URL'] =  self::relative2absolute($url, $node->getAttribute("href"));
			}

			foreach($x->query("//span[@itemprop='telephone']") as $node)
			{
				$data['PHONE'] = self::cleanup($node->textContent);
			}
			
			foreach($x->query("//div[@itemprop='address']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
			}

			foreach($x->query("//span[@class='t-stars-rating']") as $node)
			{
				$data['AVG_RATING'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//span[contains(text(),'Verified Rating')]") as $node)
			{
				$data['NUM_RATINGS'] = self::cleanup(str_replace("Verified Ratings", "", $node->textContent));
			}
		
			unset($data["RAW_ADDRESS"]);		
			$data['ACTUAL_SOURCE_URL'] =  $url;

//			log::info($data);
echo ".";
			db::store($type,$data,array('SOURCE_URL'));

		}
	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='homeadvisor' and parsed=1 ");
/*db::query("DROP TABLE homeadvisor ");
*/
$r = new homeadvisor();
$r->parseCommandLine();
