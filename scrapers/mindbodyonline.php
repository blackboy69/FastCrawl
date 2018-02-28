<?
include_once "config.inc";
include_once "search_engine_yahoo_local_scraper.php";
class mindbodyonline extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		/*	
			http://www.mindbodyonline.com/clients/xml/feed/all
			http://www.mindbodyonline.com/clients/xml/feed/country
			http://www.mindbodyonline.com/clients/xml/feed/state
			http://www.mindbodyonline.com/clients/xml/feed/featured
			http://www.mindbodyonline.com/clients/xml/feed/testimonials
		*/

		//R::freeze();
			$type= $table = get_class();		
		//db::query("DELETE FROM load_queue  where type='$type'");
		//db::query("DELETE FROM raw_data  where type='$type'");
//		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

	//	db::query("DROP TABLE  $type");
		//db::query("DELETE FROM $type");
		
			$mq = new search_engine_mapquest_api();
		$y = new search_engine_yahoo_local_scraper();

		$this->threads=1;
		$this->noProxy=false;
		$this->debug=false;

// this finds  new ones. DELETE THIS IF YOU RESCRAPE
		
$ar1 = file("mindbodyonline.new.csv");
$ar2 = file("mindbodyonline.old.csv");



var_dump(sizeof(array_diff($ar1,$ar2)));
$diff1 = array_diff($ar1,$ar2);

foreach($diff1 as $row)
{
	$r = explode("\t",$row);

	$name = $r[0] ;
	$city = $r[1] ;
	$state = $r[2] ;

	foreach(db::query("SELECT * FROM $table where NAME = '$name' and city='$city'",true) as $data)
	{
//		print_r($data);
		// next do the yahoo local
		$urls[] = $y->url($data['NAME'],$data['CITY'].", ".@$data['STATE'])."#id=".$data['id'];

	}

}
		$this->loadUrlsByArray($urls,true);
return;
// this finds  new ones. DELETE THIS IF YOU RESCRAPE  ^^^^^^^^^^^^

/* secondary parse here
foreach(db::query("SELECT * FROM $table",true) as $data)
			{
				print_r($data);
				// next do the yahoo local
				$url2load = $y->url($data['NAME'],$data['CITY'].", ".@$data['STATE'])."#id=".$data['id'];
				$this->loadUrl($url2load);
			}
			return;8
here is the originalk parse
		$url = "https://www.mindbodyonline.com/clients/feed/all.xml";
		$html = file_Get_contents($url);
*/
		/* 
			<marker>
				<marker 
					name="Studio Zen" 
					id="1397198"
					city="BIRMINGHAM" 
					state="Alabama"
					country="UNITED STATES"
					industry="Fitness"
					logo="http://clients.mindbodyonline.com/studios/StudioZen/logo.gif" 
					lat="33.4196245"
					lng="-86.6947927"/>
			</marker>
		*/
		
		$doc = new DOMDocument();
		$doc->LoadXml($html);
		$xpath = new DOMXPath($doc);
		$mq = new search_engine_mapquest_api();
		$y = new search_engine_yahoo_local_scraper();

		foreach( $xpath->query("//marker") as $node)
		{

			$data = array();
			foreach(array("name","city", "state", "country", "industry", "logo","lat","lng") as $k)
			{
				$data[$k] = $node->getAttribute($k);	
			}
		//	$data = db::normalize($data);
		//	log::info($data);
		//	$id = db::store($type, $data, array("NAME",'CITY','STATE'));

		//	$data = array_merge($data, $mq->search("{$data['LAT']}, {$data['LNG']}"));
//				$d = db::query("SELECT * FROM $table where id = $id");
			//$d = db::query("SELECT * FROM $table where id = $id AND (MATCH_TELEPHONE is null or MATCH_TELEPHONE='')");
			//if (! empty($d))
			foreach(db::query("SELECT * FROM $table",true) as $data)
			{
				//print_r($data);
				// next do the yahoo local
				$url2load = $y->url($data['NAME'],$data['CITY'].", ".@$data['STATE'])."#id=".$data['id'];
				$this->loadUrl($url2load);
			}
		}
	}

	function parse($url,$html)
	{
		$xpath = new XPATH($html);
		$mq = new search_engine_mapquest_api();
		$y = new search_engine_yahoo_local_scraper();
		$type= $table = get_class();		

		list($junk, $idFragment) = explode("#",$url);
		list($junk, $id) = explode("=",$idFragment);

		//$data = db::query("SELECT * FROM $table where id = $id AND (MATCH_TELEPHONE is null or MATCH_TELEPHONE='')");
		$data = db::query("SELECT * FROM $table where id = $id ");
		if (! empty($data))
		{
			$ydata = $y->parse($html);
			if (!empty($ydata))
			{
				foreach(db::normalize($ydata[0]) as $yd => $v)
				{
					$data["MATCH_$yd"] = $v;
				}

				unset ($data['id']); 
				unset ($data['ID']); 
				db::store($type, $data, array("NAME",'CITY','STATE'),true);
							log::info($data);
			}
		}
		log::info($data);
	

	}


	static function removeSpaces($str)
	{
			return preg_replace("/(\t|\n|\r| )+/","",$str);
	}
	
	static function cleanup($str)
	{
		while (preg_match("/  /",$str))
		{
			$str = preg_replace("/  /i"," ",$str);
		}
		return trim($str);
	}


}

//$r = new mindbodyonline();
//$r->parseCommandLine();

