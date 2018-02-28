<?
include_once "config.inc";

class findacleaningpro extends baseScrape
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


		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");	
		
*/
		//$this->proxy = "localhost:9666";


//		db::query("DELETE FROM load_queue where type='$type' and url NOT IN (SELECT url from raw_data where type='$type')");
//..		db::query("UPDATE  raw_data set parsed=0 where parsed=1 and  type='findacleaningpro' ");
//		db::query("DROP TABLE $type");	

		//$this->noProxy=true;

//
		$this->loadUrlsByZip("http://www.findacleaningpro.com/search/?dr_specialty=153&address=%ZIP%&insurance_carrier=-1&insurance_plan=-1&reason_visit=75&offset=0",25000);
	}
	
	static function parse($url,$html)
	{
		$t=self::getInstance();
		$type = get_class();	
		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		$ap = new Address_Parser();
		$pp = new phone_parser();
		$ep = new Email_Parser();
		$np = new Name_Parser();
		$urls = array();

		$data = array();
	
		$x = new Xpath($html);

		
		if (strpos($url, "findacleaningpro.com/search/?") > 0)
		{
			foreach($x->query("//a[text() = 'More Doctors']") as $node)
			{
				$urls[] = self::relative2absolute($url, $node->getAttribute("href")); // next links
			}
			
			foreach($x->query("//div[@class='docName']//a") as $node)
			{
				$urls[] = self::relative2absolute($url, $node->getAttribute("href")); // doctor listings
			}
			$t->loadUrlsByArray($urls);
		}
		else
		{

			foreach($x->query("//span[@class='docLongName']") as $node)
			{
				$data = array_merge($data, $np->parse($node->textContent));
			}

			foreach($x->query("//span[@itemprop='branchOf']") as $node)
			{
				$data['COMPANY'] = self::cleanup($node->textContent);
			}

			foreach($x->query("//div[@class='profSpecTitle']") as $node)
			{
				$data['TITLE'] = self::cleanup($node->textContent);
			}


			foreach($x->query("//div[@itemprop='aggregateRating']//meta") as $node)
			{
				$k = self::cleanup($node->getAttribute("itemprop"));
				$v = self::cleanup($node->getAttribute("content"));
				$data[$k] = $v;
			}		

			foreach($x->query("//div[@class='location-field']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
				break;
			}
			
			foreach($x->query("//div[@class='address']") as $node)
			{
				$data = array_merge($data,$ap->parse($node->c14n()));
				break;
			}



			$phones = array();	
			foreach ($pp->parse($html) as $k=>$phone)
			{
				if (preg_match("/ /",$phone) && $phone != '(855) 962-3621') 
					$phones[] = $phone;
			}
			$data = array_merge($data,$pp->parse($phones));

			//email
			//$data = array_merge($data,$ep->parse($html));
			

			$categories = array();
			foreach($x->query("//div[@class='specs']//li") as $node)
			{
				$categories[] = $node->textContent;
			}
			if (!empty($categories))
				$data['CATEGORIES'] = join(", ",$categories);

			


			unset($data["RAW_ADDRESS"]);
			$data['SOURCE_URL'] = $url;				
			

//			log::info($data);
echo ".";
			db::store($type,$data,array('SOURCE_URL'));


		}
	}
}

//db::query("UPDATE  raw_data set parsed=0 where type='findacleaningpro' and parsed=1 ");
/*db::query("DROP TABLE findacleaningpro ");
*/
$r = new findacleaningpro();
$r->parseCommandLine();
