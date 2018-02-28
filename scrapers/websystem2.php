+<?
include_once "config.inc";
error_reporting(E_ALL);

class websystem2 extends baseScrape
{
	public static $_this=null;
	public $isPost = true;

	public function runLoader()
	{
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		db::query("DROP TABLE $type");
		db::query("DELETE FROM $type");
		
		$this->threads=4;
		
		$this->debug=false;
		//		log::$errorLevel = ERROR_ALL;
		//$this->proxy = "localhost:8888";	

		$this->useCookies=false;

		for($i = 1;$i< 8000;$i++)
		{
				$this->loadUrl("http://www.websystem2.com/dradmin/news.asp?id_dr=$i");
		}
		
	}

	static function parse($url,$html)
	{
		if (strlen($html) < 500) return;

		echo ".";
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);

		$x = new HtmlParser($html);	


		foreach ($x->query("//div/table/tr/td/p/font/b/font") as $listing)
		{
			$text = $listing->c14n();
		}
		
		$sections = explode("<br></br><br></br>",$text);


		$data = self::populateData($sections);

		if (! isset($data['City']))
		{
			$data = self::populateData($sections,3,1,2);
		}

		
		foreach ($x->query("//a[text()='Visit Our Website']") as $anchor)
		{
			$data['Url'] = $anchor->getAttribute("href");
		}

		if ($data['Url'] == "http://www.")
		{
			$data['Url'] = "";
		}


		if (isset($data['Name']))
		{
			log::info($data);
			db::store($type,$data,array('Name','Phone'),true);
		}
		
	}

		
	private static function populateData($sections,$docPos=1,$addressPos = 2,$phonePos = 3)
	{
		$ap = new Address_Parser();
		

		$data = array();
		
		$data['Name'] = strip_tags($sections[0]);
		
		$doctors = explode("<br></br>",$sections[$docPos]);
				
		for($i = 0;$i<sizeof($doctors);$i++)
		{
			$data["Doctor ".($i+1)] = strip_tags($doctors[$i]);
		}

		$data = array_merge($data,$ap->parse( $sections[$addressPos] ));

		$data['Phone'] = $sections[$phonePos];

		


		$data['Full Details'] = join("<BR>",$sections);
		return $data;
	}
	
}

$r = new websystem2();
$r->runLoader();
$r->parseData();
$r->generateCSV();
