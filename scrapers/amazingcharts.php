<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class amazingcharts extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		//$this->proxy = "localhost:8888";

		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=4;
		$this->debug=false;
		
		//
	/*	

		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");

			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
	*/

		for($i=0;$i<10000;$i++)
		{
			$urls[] = "http://amazingcharts.com/ub/ubbthreads.php/users/$i";
		}
		$this->loadUrlsByArray($urls);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$data = array();
		$kvp = new KeyValue_parser();
		
		foreach($x->query("//title") as $node)
		{
			$data['NAME'] = preg_replace("/Profile for (.+) - Amazing Charts User Board/", "\\1", $node->textContent);
		}
		foreach( $x->query("//table[@class='t_inner']//tr") as $node)
		{
			// remove times
			$content = preg_replace("/([0-9]+):([0-9]+)/","\1|\2",$node->textContent);
			if (preg_match("/Posts Max Online/",$content))
				continue;
			$data = array_merge($data,$kvp->parse($content));
		}

		unset($data['NEW_BLOG']);
		unset($data['KEY']);

		if (!empty($data))
		{
			log::info($data);		
			db::store($type,$data,array('MEMBER','NAME'),true);	
		}
		else
		{
			log::error("Not Found");
			log::error($url);
		}

	}

}
$r= new amazingcharts();
$r->parseCommandLine();

