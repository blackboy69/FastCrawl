<?
include_once "config.inc";
//R::freeze();

class jll_com extends baseScrape
{
    public static $_this=null;
   public $postContentType = "Content-Type: text/xml";
   public function runLoader()
   {
		$type = get_class();		
	/*			
			$this->noProxy=false;
		$this->proxy = "localhost:8888";

			
			

			$this->useCookies=false;
			$this->allowRedirects = true;
			
			$this->debug=false;
		*/	
		//db::query("DELETE FROM LOAD_QUEUE WHERE type = '$type' and url like 'localhost%'");
		//db::query("DELETE FROM RAW_DATA WHERE type = '$type'");
		//$this->clean();
		$this->threads=1;		
		$this->timeout = 600;
		$urls = array();
		
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.us.jll.com/united-states/en-us/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.com.br/brazil/en-us/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.ca/canada/en-ca/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.cz/czech-republic/en-gb/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.fi/finland/en-gb/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.ru/russia/en-gb/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.es/spain/en-gb/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.ch/switzerland/en-gb/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.ua/ukraine/en-gb/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.co.uk/united-kingdom/en-gb/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.co.kr/korea/en-gb/people");
		$urls[]=  "http://localhost:86/casper.php?type=jll_com&p1=".urlencode("http://www.jll.nz/new-zealand/en-gb/people");		
		$this->loadUrlsByArray($urls);		
		
		//$this->loadUrl("http://www.us.jll.com/united-states/en-us/people/2301/stewart-brown");
		
		// this should get all cities. let it run for as long as it takes
		$this->timeout=6000;
		
	}
	

	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		
		// http://localhost:86/casper.php?type=jll_com&p1=Lexington,%20KY
		
		if (preg_match("#casper.php#",$url))
        {			
			$urls = array();
			
			$cleanHtml = preg_replace('#\]\s\[#',',',$html);
			$rawUrls = json_decode($cleanHtml,true);
			
			
			foreach(explode(",",$cleanHtml) as $fragment)
			{
				$urls[] = self::relative2absolute("http://www.us.jll.com", trim(preg_replace('#(,|"|\[|\]| )#',"",$fragment)));
			}		
			log::info($urls);
			if (sizeof($urls)>0)			
					$thiz->loadUrlsByArray($urls);		
        }
		else // parse person details page
		{
			$data = array();
			$x =  new  XPath($html);			

			
			foreach ($x->query("//div[contains(@class, 'profile')]//h2") as $node)
			{	
				$data = array_merge($data, $np->parse($node->textContent));
			}
			
			
			foreach ($x->query("//div[contains(@class, 'profile')]//p[@class='bio-relicensenumber']") as $node)
			{	
				$data['LICENSE_NUMBER'] = $node->textContent;
			}
						
			foreach ($x->query("//div[contains(@class, 'profile')]//p[@class='bio-address']") as $node)
			{	
				$data = array_merge($data, $ap->parse($node->textContent));
			}
			
			foreach ($x->query("//div[contains(@class, 'profile')]") as $node)
			{	
				// format +1 305 529 6343
				//$data = array_merge($data, $pp->parse($node->textContent));
				$data = array_merge($data, $ep->parse($node->textContent));
			}
			
			$phones = array()			;
			foreach ($x->query("//div[contains(@class, 'profile')]//p") as $node)
			{	
				// format +1 305 529 6343
				//$data = array_merge($data, $pp->parse($node->textContent));
				if (preg_match("#[0-9]+ [0-9]+ [0-9]+#",$node->textContent))
					$phones = trim($node->textContent);			
			}
			$data['PHONE'] = $phones;
			
			
			foreach ($x->query("//div[contains(@class, 'main')]//p") as $node)
			{	
				$x2 = new Xpath($node);
				$key = "";
				foreach($x2->query("//strong") as $node2)
				{
					$key = trim($node2->textContent);
				}
				$value = $node->textContent;				
				$value = str_replace($key, "",$value);
				
				if (!empty($key))
					$data[$key] = trim($value);
			}
			
						
			$data2 = db::normalize($data);
			unset($data);
			$data = array();
			foreach ($data2 as $k=>$v)
			{
				if (empty($v) || empty($k))// cleanup
					continue;
				if (preg_match("/__/", $k))// two spaces is a no no
					continue;
				if (strlen($k)>25) // too long get outta here
					continue;
					
				$data[$k] = $v;
			}
				
				
			$data["SOURCE URL"] = $url;
			log::info($data);			
			
			db::store($type,$data,array('FIRST_NAME','LAST_NAME','ADDRESS','CITY'),true);	

		}
					
	}
}

$r= new jll_com();
$r->parseCommandLine();

