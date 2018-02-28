
<?
include_once "config.inc";
//R::freeze();

class bbb_right_at_home extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;		
		$this->threads=1;		
		
		$this->loadUrl("http://www.bbb.org/search/?type=name&input=Right+at+Home");
	}


   public static function parse($url,$html)
   {
      $type = get_class();    
      $thiz = self::getInstance();

      $ap = new Address_Parser();
      $pp = new phone_parser();
      $ep = new Email_Parser();
      $kvp = new KeyValue_Parser(); 
      $urls = array();
      log::info($url);
      //parse_str(parse_url($url,PHP_URL_QUERY),$query); 

   // file_put_contents("$type.html",$html); 
      $webRequests = array();
      $links = array();
      $data = array();
		$x = new Xpath($html);

		if (preg_match("#http://www.bbb.org/search/?#",$url))
		{
			$urls = array();
			
			foreach($x->query("//ul[@class='business-links']//a") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
			}	
			// next page links
			
			foreach($x->query("//ul[@class='pagination']//a") as $node)
			{
				$urls[]=  self::relative2Absolute($url,$node->getAttribute("href"));
			}

			$thiz->loadUrlsByArray($urls);
		}
		else
		{
			foreach ($x->query("//h1[@class='business-title']") as $node)
			{	
				$data['Name'] = self::cleanup($node->textContent);
			}

			if (isset($data['Name']))
			{
				$ap = new Address_Parser();
				foreach ($x->query("//span[@class='business-phone']") as $node)
				{	
					$data['Telephone'] = str_replace("\n",", ", str_replace("Phone: ", "", $node->textContent));
				}


				foreach ($x->query("//span[@class='business-fax']") as $node)
				{	
				//	$data['Fax'] = str_replace("Fax: ", "", $node->textContent);
				}
				
				foreach ($x->query("//span[@class='business-email']//a") as $node)
				{	
					$data['Email'] = trim($node->textContent);
				}
				$address = array();
				foreach ($x->query("//span[@class='business-address']//span") as $node)
				{				
					$address[] = $node->textContent;
				}
				$data = array_merge($data,$ap->parse( join(", ", $address) ));

				foreach ($x->query("//span[@class='business-link']//a") as $node)
				{	
					$data['Url'] = $node->getAttribute("href");
				}

				foreach ($x->query("//div[@id='complaint-sort-container']") as $node)
				{	
					if (preg_match("/complaints/i",$node->textContent))
						list($data['NUM_COMPLAINTS'], $junk) =  explode(" ", self::cleanup($node->textContent));

					if (preg_match("/reviews/i",$node->textContent))
						list($data['NUM_REVIEWS'], $junk) =  explode(" ",self::cleanup($node->textContent));
				}

				foreach ($x->query("//div[@id='accedited-rating']//img") as $node)
				{	
					$title = $node->getAttribute("title");
					if (empty($title))
						$data['BBB Rating'] =  $node->getAttribute("alt");
				}

				
				foreach ($x->query("//div[@id='accedited-rating']//span[@itemprop='ratingValue']") as $node)
				{	
					$data['BBB Rating Value'] = $node->textContent;
				}
				
				foreach ($x->query("//div[@id='business-additional-info-text']//span") as $node)
				{
					$span = $node->textContent;
					if (preg_match("/^(.+):(.+)/",$span,$matches))
					{
						$k = strtoupper(trim($matches[1]));

						if (strpos($k,":")>0)
							continue;

						if (strlen($k)<20)
							$data[$k] = trim($matches[2]);
					}
				}
				
				$contacts = 1;

				foreach ($x->query("//span[@class='employees']//span[@itemtype='http://schema.org/Person']") as $node)
				{	

					$x2 = new HtmlParser($node->c14n());	

					foreach($x2->query("//span[@itemprop='name']") as $nameNode)
					{
						$name = $nameNode->textContent;
					}

					foreach($x2->query("//span[@itemprop='jobTitle']") as $jobTitleNode)
					{
						$jobTitle = trim(preg_replace("/[^0-9a-zA-Z_ ]/","", $jobTitleNode->textContent));
					}

					if (empty($jobTitle))
					{
						$jobTitle = "Owner";
					}							
					$data[trim("CONTACT INFO ".$contacts++)] = "$name ($jobTitle)";
					if ($contacts > 20) break;
				}

				foreach ($x->query("//span[@id='lblContact']") as $node)
				{
					$data[trim("CONTACT INFO ".$contacts++)] = $node->textContent;;
					if ($contacts > 20) break;

				}
				$data['SOURCE_URL'] = $url;

				$urlParts = explode("/", parse_url($url,PHP_URL_PATH));
				$category = $urlParts[3];
				$category = ucfirst(str_replace("-"," ",$category));
				$data['Category']=$category;

				unset($data['Raw Address']);
				log::info($data);
				
				//file_put_contents("d:/dev/demandforce/last.html",$html);
			
				db::store($type,$data,array('SOURCE_URL'),false);
				return true;
			}
		}
   }
}


$r= new bbb_right_at_home();
$r->parseCommandLine();

