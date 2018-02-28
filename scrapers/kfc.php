<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class kfc extends baseScrape
{
    public static $_this=null;
	
	
   public function runLoader()
   {
		$type = get_class();		
	$this->threads=10;
	  foreach(db::query("SELECT zip FROM geo.locations order by pop desc",true) as $row)
      {
		  $zip = sprintf("%05d",$row['zip']);
		  
			$url = "https://services.kfc.com/services/query/locations?{$zip}";
			$toPost = "address={$zip}&distance=250";
			$webRequests[] = new WebRequest($url,$type,"POST",$toPost);
      }
      log::info ("Loaded ".sizeof($webRequests)." urls by zip code for $type");
      return  $this->loadWebRequests($webRequests);
	}


	public static function parse($url,$html)
	{
		$type = get_class();		

		$json_data = json_decode($html,true);
		log::info(" Loaded " .sizeof($json_data["results"] )." " .$url );	
		foreach ($json_data["results"] as $data)
		{
			$data['SOURCE_URL'] = $url;
							
			db::store($type,$data,array('entityID'));	
		}

	}
	
}

$r= new kfc();
$r->parseCommandLine();

