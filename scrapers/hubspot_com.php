<?
include_once "config.inc";

class hubspot_com extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		/*	
			
			$this->proxy = "localhost:8888";

			$this->useCookies=false;
			$this->allowRedirects = true;
			
			$this->debug=false;
		*/	

		
		//$this->threads=2;
		$this->maxRetries=3;
		
		$this->clean(false);
		//db::query("DELETE FROM RAW_DATA WHERE type ='$type'");
		
		$options = urlencode('{"pageSize":15,"sortType":"RATING","orderOfSortType":"DESC","providerType":"MARKETING_SERVICES"}');
		$this->loadUrl("HTTPS://api.hubapi.com/partners/v1/directory/page?options=$$options");
		
		foreach (array("MARKETING_SERVICES","COS_DESIGN",) as $svc)
		{
			$options = urlencode('{"pageSize":15,"sortType":"RATING","orderOfSortType":"DESC","providerType":"'.$svc.'"}');
			$this->loadUrl("HTTPS://api.hubapi.com/partners/v1/directory/page?options=$options");
		}
		
	}


	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 
		
		$optionsIn = json_Decode(urldecode($query['options']),true);

		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		
		if (preg_match("#api.hubapi.com#",$url))
		{
			
			$json = json_decode($html,true);
			
			foreach($json['providers'] as $data)
			{	
				$data['XID'] = $data['id'];
				unset($data['id']);
				unset($data['portalId']);
				$data['PROVIDER_TYPE'] = $optionsIn['providerType'];
			
				$data['SOURCE_URL'] = $url;
				
				$id = db::store($type,$data,array('XID'));	
				log::info($data);
				if (!empty($data['url']))
					$thiz->loadUrl($data['url']."?id=$id");
			}
			// are there more loistings? 
			if ($json['containsMore'])
			{
				// last provider is already in $data
				$options = urlencode(json_encode(array(
											"pageSize"=>15,
											"lastName"=>$data['name'],
											"lastRating"=>$data['rating'],
											"lastSortRating"=>$data['sortRating'],
											"lastNumberOfReviews"=>$data['numberOfReviews'],
											"sortType"=>"RATING",
											"orderOfSortType"=>"DESC",
											"providerType" =>$optionsIn['providerType']
											)));
				$thiz->loadUrl("HTTPS://api.hubapi.com/partners/v1/directory/page?options=$options");
			}
		}
		else // check the id
		{
			if (!empty($query['id']))
			{
				$id = $query['id'];

				$data = db::query("SELECT * FROM $type where id = $id");
				$data=array_merge($data,$ep->parse(strip_tags($html)));
				$data=array_merge($data,$pp->parse(strip_tags($html)));
				
				log::info("!!!SECONDARY PARSE!!!!");

				// did we find email or phone numbers?
				if ( isset($data['EMAIL']) )
				{			
					log::info($data);
					db::store($type,$data,array('XID'),true);
				}
				// otherwise spider to the contact us page when both aren't already set.
				else 
				{
					$x = new  XPath($html);	

					foreach($x->query("//a[contains( translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'contact')]") as $node)
					{
						$href = self::relative2absolute($url,$node->getAttribute("href")) ;						
						log::info("Found Contact us page");
						log::info($href);
						$thiz->loadUrl($href."?id=$id");						
					}

					foreach($x->query("//a[contains( translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'about')]") as $node)
					{
						$href = self::relative2absolute($url,$node->getAttribute("href")) ;						
						log::info("Found about us page");
						log::info($href);
						$thiz->loadUrl($href."?id=$id");						
					}
				}
			}	
		}			
		
	}
}

$r= new hubspot_com();
$r->parseCommandLine();

