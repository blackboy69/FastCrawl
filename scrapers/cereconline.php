<?
include_once "config.inc";
error_reporting(E_ALL);

class cereconline extends baseScrape
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
		//db::query("DELETE FROM $type");
		
		$this->loadUrl("http://whitlock.ath.cx/demandforce/cereconline");
	}

	static function parse($url,$html)
	{
		echo ".";
		$type = get_class();		
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);

		$x = new HtmlParser($html);	

		foreach ($x->query("//p") as $listing)
		{
			$text = $listing->c14n();

			$x2 =  new HtmlParser($text);	
			$sections = explode("<br></br>",$text);

			$data = self::populateData($sections);
			if (! isset($data['Zip']))
			{
				$data = self::populateData($sections,2,4);
			}

			if ( empty($data['Name']))
			{
				$data['Name'] = $sections[1];
			}


				log::info($data);
				db::store($type,$data,array('Name','Phone'),false);

		}		
	}

		
	private static function populateData($sections, $addressPos = 1,$phonePos = 3)
	{
		$ap = new Address_Parser();
		

		$data = array();
		
		$data['Name'] = strip_tags($sections[0]);
		
		$data = array_merge($data,$ap->parse( $sections[$addressPos] . " ,". $sections[$addressPos+1] ));

		$data['Phone'] = $sections[$phonePos];
		
		$data['Url'] = strip_tags($sections[$phonePos+1]);
		$data['Email'] = strip_tags($sections[$phonePos+2]);
		if (preg_match("/[0-9]+ miles/", $data['Email']))
		{
			$data['Email'] = "";
		}
		$data['Full Details'] = join("<BR>",$sections);
		return $data;
	}
	
}

$r = new cereconline();
$r->runLoader();
$r->parseData();
$r->generateCSV();
