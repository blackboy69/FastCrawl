<?
include_once "config.inc";
error_reporting(E_ALL);

class techauto extends baseScrape
{
	public static $_this=null;
	public $isPost = false;

	public function runLoader()
	{
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' ");
	//	db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		//db::query("DROP TABLE $type");
		//db::query("DELETE FROM $type");
		
		//$this->loadUrl("http://whitlock.ath.cx/demandforce/techauto");
		$sql = "
			SELECT TRUNCATE( lat, 1 ) as lat , TRUNCATE( lon, 1 ) as lon 
			FROM geo.locations
			WHERE state NOT
			IN (
			'AK', 'PR', 'HI'
			)
			GROUP BY TRUNCATE( lat, 1 ) , TRUNCATE( lon, 1 ) 
			HAVING max( pop ) >1000
		";
		$i=0;
		$rows= db::query($sql,true);
		
		foreach( $rows as $row)
		{
			$lat = $row['lat'];
			$lon = $row['lon'];
			
			$url = "http://www.techauto.com/phpsqlsearch_genxml.php?lat=$lat&lng=$lon&radius=250";
			$this->loadUrl($url);

		}

		$this->loadUrl("http://www.techauto.com/phpsqlsearch_genxml.php?lat=38.2&lng=-122.7&radius=250");

		$this->numThreads=10;
	}

	static function parse($url,$xml)
	{
		echo ".";

		$type = get_class();		
		$dom = new DOMDocument();	
		$dom->loadXml($xml);
		$x = new DOMXPath($dom);	
		$a = new Address_Parser();

		foreach ($x->query("//marker") as $listing)
		{
			$data['Name'] = $listing->getAttribute("name");
			
			
			$data = array_merge($data, $a->parse($listing->getAttribute("address")));
			$data['Phone'] = $listing->getAttribute("phone");
			$data['Url'] = $listing->getAttribute("website");

			$feature = $listing->getAttribute("feature");
			if (!empty($feature))
			{
				$x = new HtmlParser($feature);
				foreach($x->query("//img") as $f)
				{
					$service = basename($f->getAttribute("src"),'.png');
					$data[$service] = 'Yes';
				}
			}			

			$data['longitude'] = $listing->getAttribute("lng");
			$data['latitude'] = $listing->getAttribute("lat");

			
				log::info($data);
				db::store($type,$data,array('Name','Phone'),false);

		}		
	}

		
}

$r = new techauto();
$r->runLoader();
$r->parseData();
$r->generateCSV();
