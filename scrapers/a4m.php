<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class a4m extends baseScrape
{
    public static $_this=null;
	

   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
		$this->proxy = "localhost:8888";

		$this->useCookies=true;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		 //db::query("delete from load_queue where url like 'http://www.a4m.com/directory%'");
		 //zdb::query("delete from raw_data where url like 'http://www.a4m.com/directory%'");

//db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
/*	
		db::query("DELETE FROM raw_data where url like 'http://www.a4m.com/directory;catalog,directory,,%'");
				db::query("DELETE FROM load_queue where url like 'http://www.a4m.com/directory;catalog,directory,,%'");

	
		db::query("DROP TABLE a4m");
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
		$this->setReferer("http://www.a4m.com/directory.html","http://www.a4m.com");
		$sf = $this->Get("http://www.a4m.com/directory.html");
		$x = new HtmlParser($sf);
		$post = $x->GetForm("directory_search");
		$post = $post[0];
		$postUrl  = $post['action'];
		unset($post['keyword']);
		unset($post['action']);
		unset($post['']);
				unset($post['city']);
						unset($post['zip_code']);

unset($post['property_option[]']);
		$post['distance']=10;
		
		Log::info("Loading....");
		$this->setReferer("http://www.a4m.com/directory.html","http://www.a4m.com/directory.html");
		$req = new WebRequest("http://www.a4m.com/directory.html",$type,"POST",$this->buildQuery($post,2)."&search_in_category[]=directory_professionals");			

		$this->loadWebRequest($req,true);
		$this->queuedPost();


		for ($i=0;$i<140;$i++)
			$toload[]  = "http://www.a4m.com/directory;catalog,directory,,$i.html";
		
		$this->loadUrlsByArray($toload);
		$this->queuedPost();

/*
$this->setReferer("http://www.a4m.com/directory;catalog,directory,,10.html",  "http://www.a4m.com/directory.html");
$this->setReferer("http://www.a4m.com/directory;catalog,directory,,14.html",  "http://www.a4m.com/directory;catalog,directory,,10.html");
$this->setReferer("http://www.a4m.com/directory;catalog,directory,,18.html",  "http://www.a4m.com/directory;catalog,directory,,14.html");
$toload=array();
$toload[]  = "http://www.a4m.com/directory;catalog,directory,,10.html";
$toload[]  = "http://www.a4m.com/directory;catalog,directory,,14.html";
$toload[]  = "http://www.a4m.com/directory;catalog,directory,,18.html";

		$this->loadUrlsByArray($toload);
				$this->queuedFetch();
				*/
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
		
		// l0ok for nexct page link
		foreach($x->query("//div[contains(@class,'directory_navigation_top')]/div/div[contains(@class,'directory_paging_main')]//a[last()]") as $node)
		{
			$urls[] = $newurl = $thiz->relative2Absolute($url, $node->getAttribute("href"));
			$thiz->setReferer($newurl, $url);
		}
		foreach($x->query("//div[@class='directory_list_item_details']/h2/a") as $node)
		{
			 $urls[]  = $thiz->relative2Absolute($url, $node->getAttribute("href"));
		}

		if (!empty($urls))
		{			
			$thiz->LoadUrlsByArray($urls);
		}
		//else
		{
			foreach($x->query("//div[@class='directory_detail_profile_name']/h1") as $node)
			{
				$data['NAME'] = $node->textContent;	
			}

			foreach($x->query("//div[@class='directory_detail_profile_name']/h2") as $node)
			{
				$data['PRACTICE'] = $node->textContent;	
			}

			foreach($x->query("//div[@class='directory_detail_profile_address']") as $node)
			{
				$address = $node->c14n();	
				$data = array_merge($data,$ap->parse($address));
			}

			foreach($x->query("//div[@class='directory_detail_profile_phone']") as $node)
			{
				$data['PHONE'] = $node->textContent;	
			}

			foreach($x->query("//div[@class='directory_detail_profile_phone_2']") as $node)
			{
				$data['PHONE2'] = $node->textContent;	
			}
			
			foreach($x->query("//div[@class='directory_detail_profile_phone_3']") as $node)
			{
				$data['PHONE3'] = $node->textContent;	
			}			

			
			foreach($x->query("//div[@class='directory_detail_profile_website']//a") as $node)
			{
				$data['WEBSITE'] = $website= $node->getAttribute("href");	
				$d = new $type();
				$d->Get($website);
				$urls  = array_keys($d->responseHeaders);
				$data['WEBSITE'] = end($urls);
				break;
			}			
									
			
			$data['SOURCE_URL']  = $url;
			if (isset($data['NAME']) || isset($data['PRACTICE']))
			{
				log::info($data);		
				db::store($type,$data,array('NAME','PRACTICE','SOURCE_URL'),true);	
			}
		}
		
	}

}

$r= new a4m();
$r->parseCommandLine();

