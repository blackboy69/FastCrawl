<?
include_once "config.inc";
//R::freeze();

class ngkf_com extends baseScrape
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
		
		$this->loadUrl("http://www.ngkf.com");
		
	}
	

	public static function parse($url,$html)
	{
		log::info($url);
		$type = get_class();		
		$thiz = self::getInstance();
		$urls = array();
		parse_str(parse_url($url,PHP_URL_QUERY),$query); 
		
		$data = array();
		$x =  new  XPath($html);			
		$ap = new Address_Parser();
		$pp=new Phone_Parser();
		$ep=new Email_Parser();
		$kvp = new KeyValue_Parser();
		$np = new Name_Parser();
		
		if ($url == "http://www.ngkf.com/")
        {			
			
			
			foreach($x->query("//select[contains(@id,'drpList')]//option") as $node)
			{
				$urls[] = self::relative2absolute($url, $node->getAttribute("value"));
			}		
			log::info($urls);
			if (sizeof($urls)>0)			
				$thiz->loadUrlsByArray($urls);		
        }
		else if (preg_match("#professional-profiles.aspx#",$url)) // parse person details page
		{
			foreach($x->query("//a[contains(@id,'hypContactName')]") as $node)
			{
				$urls[] = self::relative2absolute($url, $node->getAttribute("href"));
			}		
			
			if (sizeof($urls)>0)	
			{				
				$thiz->loadUrlsByArray($urls);		
				return;
			}
			
			foreach ($x->query("//div[@class= 'detail-info']//span") as $node)
			{	
				$data = array_merge($data, $np->parse($node->textContent));
				break;
			}
			
			foreach ($x->query("//div[@class= 'detail-info']/span/following-sibling::text()") as $node)
			{	
				$data['TITLE'] = trim($node->textContent);
			}
			
			foreach ($x->query("//div[@style= 'padding-top: 7px; padding-bottom: 7px;']") as $node)
			{	
				$htmlFragment = $node->c14n();
				$hfList = explode("<br>", $htmlFragment);
				
				$data['COMPANY_NAME'] = trim(strip_tags($hfList[0]));
				
				$data = array_merge($data, $pp->parse($node->textContent));
				$data = array_merge($data, $ap->parse($node->textContent));
			}
			
			foreach ($x->query("//a[contains(@id, 'hypEmailLink')]") as $node)
			{	
				/* <a id="MasterPage_ctl00_ContentPlaceHolder1_hypEmailLink" 
					href="javascript:mailTo('rnarkiewicz', 'ngkf.com');">
						<img src="/images/TextImage.ashx?text=rnarkiewicz@ngkf.com" style="border-width:0px;">
					</a>
					*/
					$href = $node->getAttribute("href");

					$href = str_replace("javascript:mailTo('","",$href);
					$href = str_replace("');","",$href);
					$href = str_replace("', '","@",$href);
					
					$data['EMAIL'] = $href;
				
			}
			
			
			foreach ($x->query("//div[contains(@class, 'AreasOfSpecialization')]") as $node)
			{	
				$data['Areas Of Specialization'] = $node->textContent;
			}
						
			foreach ($x->query("//div[contains(@class, 'divRightColumn')]//p[contains(text(),'Years of Experience')]//following-sibling::text()") as $node)
			{	
				$data['Years of Experience'] = trim($node->textContent);
			}
			
			foreach ($x->query("//div[@class= 'body-txt']") as $node)
			{	
				$data['PROFESSIONAL_PROFILE'] = trim($node->textContent);
			}
			
			$data = db::normalize($data);			
				
			$data["SOURCE URL"] = $url;
			log::info($data);			
			
			db::store($type,$data,array('FIRST_NAME','LAST_NAME','ADDRESS','CITY'),true);	

		}
					
	}
}

$r= new ngkf_com();
$r->parseCommandLine();

