<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class intuit_health extends baseScrape
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
		$this->timeout=5;
		
		//
	/*			db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");		
				db::query("Drop table $type");
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");

			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");		
		
		/index.cfm/fuseaction/site.locations/action/list.cfm
	*/
		$sites = db::oneCol("Select WebSite_ContactUs from powered_by_intuit_health");
		$this->loadUrlsByArray($sites);
	}

	public static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		$x = new XPath($html);	
		$data = array();
		$kvp = new KeyValue_Parser();
		$ap = new Address_Parser();
		
		foreach($x->query("//p[@class='ts_sub_title']") as $node)
		{
			$data['NAME'] = $node->textContent;
		}

		$kv = array();	
		foreach($x->query("//p[@class='ts_page_content']") as $node)
		{
			$kv = "";
			foreach( explode('<font class="ts_topic_header">',$node->c14n()) as $row)
			{
				$kv .= strip_Tags($row)."<Br>\n";
			}			
		}
		if(!empty($kv))
		{
			$data = array_merge($data,$kvp->parse($kv));
		}
		$data = db::normalize($data);
		if ( isset($data['ADDRESS']))
		{
			$data = array_merge($data,$ap->parse($data['ADDRESS']));
		}

		$data["WEBSITE_CONTACTUS"] = $url;
		if (!empty($data))
		{
			log::info($data);		
				db::store($type,$data,array('WebSite_ContactUs'));	
		}
	}

	public function csvSql()
	{
		return array(get_class($this)."_full" => "
			SELECT h.*,p.*
			FROM intuit_health h
			RIGHT JOIN powered_by_intuit_health p ON p.WebSite_ContactUs = h.WebSite_ContactUs
		",
			get_class($this)."_filtered" => "
			SELECT *
			FROM intuit_health h
			INNER JOIN powered_by_intuit_health p ON p.WebSite_ContactUs = h.WebSite_ContactUs
			WHERE NAME is not null AND NAME <> ''
		",
		);
	}

}

$r= new intuit_health();
$r->parseCommandLine();

