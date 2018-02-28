<?
include_once "config.inc";
//R::freeze();

class transwestern_com extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;		
		$this->threads=1;		
		
		foreach(range('A', 'Z') as $i)
			$urls[] = "https://www.transwestern.com/search/peopleresults?search=$i&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=property&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=president&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=director&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=sales&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=engineer&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=texas&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=california&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=new+york&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=AZ&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=WI&limit=2500";
		$urls[] = "https://www.transwestern.com/search/peopleresults?search=smith&limit=2500";
		
		
		$this->loadUrlsByArray($urls);
	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$np=new Name_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();		
		$x = new XPath($html);	
		$data = array();
		if (preg_match("#search/peopleresults#",$url))
		{
			
			$urls = array();
			foreach ($x->query("//a[contains(@href, '/bio?')]") as $node)
			{				
				$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			
			// next page links			
			foreach ($x->query("//a[contains(@href, 'peopleresults?')]") as $node)
			{				
				$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
			}
			if (sizeof($urls)>0)			
					$thiz->loadUrlsByArray($urls);
		}
		else
		{		
		
			// get name
			foreach ($x->query("//span[@class='OpenSansCondBlue36UpperBold']") as $node)
			{	
				$data = array_merge($data, $np->parse($node->textContent));
				break;
			}
			
			// get title
			foreach ($x->query("//span[@class='OpenSansCondBlack24UpperBold'][1]") as $node)
			{	
				
				$data['TITLE']=trim(strip_tags($node->textContent));
			}
			foreach ($x->query("//span[@class='OpenSansCondBlack24UpperBold'][2]") as $node)
			{	
				
				$data['SPECIALITIES']=trim(strip_tags($node->textContent));
			}
			
				
			// get profile
			foreach ($x->query("//div[@class='twoThirdColumn_bio']") as $node)
			{				
				$data['PROFESSIONAL_PROFILE'] = @Html2Text::convert($node->c14n());
				break;
			}
			
			
			// address
			foreach ($x->query("//span[@class = 'OpenSansBlack10']") as $node)
			{	
				$data = array_merge($data, $ap->parse($node->textContent));
				$data = array_merge($data, $ep->parse($node->textContent));
				$data = array_merge($data, $pp->parse($node->textContent));
			}
									
			$data = db::normalize($data);			
				
			$data["SOURCE URL"] = $url;
			log::info($data);			
			
			db::store($type,$data,array('FIRST_NAME','LAST_NAME','RAW_ADDRESS'),true);	

		}
	}
}

$r= new transwestern_com();
$r->parseCommandLine();

