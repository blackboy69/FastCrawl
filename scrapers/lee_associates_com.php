<?
include_once "config.inc";
//R::freeze();



class lee_associates_com extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;		
		$this->threads=1;		
		
		$this->loadUrl("http://resources.lee-associates.com/asp/user/website/ourpeople.asp?brokerFirstName=&brokerLastName=&specialtyDivision=&officeLocation=&submitPage=Yes&btnSearch.x=68&btnSearch.y=14");
	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$np=new Name_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();		
		
		
		if (preg_match("#/ourpeople.asp#",$url))
		{
			$xOuter = new XPath($html);	
			foreach($xOuter->query("//tr[@class='trStyle']") as $nodeOuter)
			{
				$data = array();
				$x = new xpath($nodeOuter);
				
				foreach ($x->query("//td[@width='178']") as $node)
				{
					$data['NAME']=$node->textContent;
				}
				foreach ($x->query("//td[@width='175']") as $node)
				{
					$data['TITLE']= self::cleanup($node->textContent);
				}
				foreach ($x->query("//td[@width='237']") as $node)
				{
					$data['EMAIL']=$node->textContent;
				}
				foreach ($x->query("//td[@width='113']") as $node)
				{
					$data['PHONE']=$node->textContent;
				}
				foreach ($x->query("//td[@width='178']//a") as $node)
				{
					parse_str(parse_url($node->getAttribute("href"),PHP_URL_QUERY),$q); 
					$data['BROKER_ID']=$q['BrokerID'];
					
					// load resume					
					$data['RESUME_URL']="http://resources.lee-associates.com/asp/user/website/BrokerResume.asp?BrokerID=".$q['BrokerID'];					
					$thiz->loadUrl( $data['RESUME_URL']);
					
				}	
				
				$data = db::normalize($data);			
					
				$data["SOURCE_URL"] = $url;
				log::info($data);			
				
				db::store($type,$data,array('BROKER_ID'),true);	
			}
		}
		if (preg_match("#/BrokerResume.asp#",$url)) 
		{
			$x = new XPath($html);	
			$data = array();
			
			foreach ($x->query("//h1//span[@class='first']") as $node)
			{
				$data['FIRST_NAME']=$node->textContent;
			}
			foreach ($x->query("//h1//span[@class='last']") as $node)
			{
				$data['LAST_NAME']=$node->textContent;
			}
			foreach ($x->query("//h1//span[@class='suffix']") as $node)
			{
				$data['SUFFIX']=$node->textContent;
			}			
			foreach ($x->query("//h2[@id='title']") as $node)
			{
				$data['TITLE']=$node->textContent;
			}
						
			foreach ($x->query("//div[@id='profile']") as $node)
			{			
				$data = array_merge($data, $ap->parse($node->textContent));
				$data = array_merge($data, $ep->parse($node->textContent));
				$data = array_merge($data, $pp->parse($node->textContent));
			}
						
			foreach ($x->query("//div[@id='clientList']//li") as $node)
			{		
				$clients[] = $node->textContent;
			}
			$data['Client List'] = join(", ",$clients);
			
			foreach ($x->query("//div[@id='content']") as $node)
			{
				$data['BIO']=$node->textContent;
			}
			
			parse_str(parse_url($url,PHP_URL_QUERY),$urlQuery); 
			
			$data["BROKER_ID"] = $urlQuery['BrokerID'];
			$data["SOURCE_URL"] = $url;
			$data = db::normalize($data);						
			log::info($data);						
			db::store($type,$data,array('BROKER_ID'),true);					
		}
	}
}

$r= new lee_associates_com();
$r->parseCommandLine();

