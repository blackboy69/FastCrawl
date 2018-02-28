<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class nextgen extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
		$this->proxy = "localhost:8888";
		unlink($this->cookie_file);
		$this->useCookies=true;
		$this->allowRedirects = TRUE;
		$this->threads=4; // redirection got me fucked up. can only use one thread perprocessess.
		$this->debug=false;
		 //db::query("delete from load_queue where url like 'http://www.nextgen.com/directory%'");
		 //zdb::query("delete from raw_data where url like 'http://www.nextgen.com/directory%'");

//db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
/*	
		db::query("DELETE FROM raw_data where url like 'http://www.nextgen.com/directory;catalog,directory,,%'");
				db::query("DELETE FROM load_queue where url like 'http://www.nextgen.com/directory;catalog,directory,,%'");

	
		db::query("DROP TABLE nextgen");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");

		//
		
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
			db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		

		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
		
		db::query("DELETE FROM load_queue ");
		db::query("DELETE FROM raw_data ");		
			
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");	*/	

		for ($i=0;$i<28500;$i++)
			$toload[]  = "http://www.nextgen.com/Community/Profile/Public.aspx?ProfileID=$i";
		
		$this->loadUrlsByArray ($toload);
//		self::login();

// login with the browser copy the headers easier that way.
		$this->cookieData = 'Cookie: CoreID6=56260502600113378356306&ci=90374216; __kti=1337835631397,http%3A%2F%2Fwww.nextgen.com%2FCommunity%2FForum%2FForumMain.aspx%3FRequestId%3D50da2faf,; __ktv=dde6-b0ca-3279-ed91377d37b326; ASP.NET_SessionId=mwq12lwjk0tp2iag0q4gjyt3; cmTPSet=Y; __kts=1338368840788,http%3A%2F%2Fwww.nextgen.com%2FCommunity%2FProfile%2FSearch.aspx%3FQ%3D%26Token%3Df12ea7ad%26RequestId%3D3693076a,; __ktt=25af-1e6-b1f5-4f181379cffd454; __utma=116056601.1503894801.1337835631.1338362741.1338368841.7; __utmb=116056601.16.10.1338368841; __utmc=116056601; __utmz=116056601.1337835631.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); _mkto_trk=id:077-SCT-843&token:_mch-nextgen.com-1337835631832-91365; 90374216_clogin=l=1338368839&v=1&e=1338371606430';

				$this->queuedFetch();
	}

	public static function parse($url,$html)
	{
		

		log::info("Parsing $url");
		$type = get_class();		
		$thiz = self::getInstance();
		
		$x = new XPath($html);	
		$data = array();
		$kvp = new KeyValue_Parser();
		$ap = new Address_Parser();
		
		$urls = array();
		foreach($x->query("//span[@class='PositionTitle']") as $node)
		{
			$data['COMPANY_TITLE'] = $node->textContent;
		}

		foreach($x->query("//span[@class='PageTitle']") as $node)
		{
			$data['NAME'] = $node->textContent;
		}

		foreach($x->query("//span[contains(@id, 'CurrentStatus')]") as $node)
		{
			$data['STATUS'] = $node->textContent;
		}
		
		foreach($x->query("//*[@class='toggle_container']") as $node)
		{
			$kvdata = explode("|", strip_tags(str_replace("</tr>","|", ($node->c14n()))));
			$data = array_merge($data,$kvp->parse($kvdata));

		}
	
		$data['SOURCE_URL']  = $url;
		if (isset($data['NAME']))
		{
			log::info($data);		
			db::store($type,$data,array('NAME', 'COMPANY_TITLE', 'SOURCE_URL'));	
		}
		
	
	
	}

}

$r= new nextgen();
$r->parseCommandLine();

