<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class botox extends baseScrape
{
    public static $_this=null;
	
	
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=false;
		//$this->proxy = "localhost:8888";

//		$this->useCookies=false;
		$this->allowRedirects = true;
		$this->threads=1;
		$this->debug=false;
		
		//
	/*	
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		
			
		
		db::query("DELETE FROM load_queue where type='$type'");
		db::query("DELETE FROM raw_data where type='$type'");			
*/	

		$productIdMap[1] = 'botoxcosmetic';
		$productIdMap[2] = 'juvederm';
		$productIdMap[3] = 'natrelle';
		$productIdMap[7] = 'latisse';
		$productIdMap[10] = 'viviteskincare';

		$geos = db::query("SELECT zip, max(lat) as lat ,max(lon) as lon from geo.locations where pop>20000 group by zip",true);
		$urls = array();
		foreach($geos as $geo)
		{
			$zip = $geo['zip'];
			$lat = $geo['lat'];
			$lon = $geo['lon'];
			foreach ($productIdMap as $productId => $product)
			{
				$urls[]="http://www.botoxcosmetic.com/App_Controls/FindADoc/WebServices/ws_DocList.ashx?r=2.2&SearchType=1&CenterLatitude=$lat&CenterLongitude=$lon&Radius=15&ProductID=$productId&ZipCode=$zip&SortBy=0&showPictureID=true";
			}
		}		
		$this->loadUrlsByArray($urls);
	}


	public static function parse($url,$html)
	{

		$productIdMap[1] = 'botoxcosmetic';
		$productIdMap[2] = 'juvederm';
		$productIdMap[3] = 'natrelle';
		$productIdMap[7] = 'latisse';
		$productIdMap[10] = 'viviteskincare';


		$type = get_class();		
		$thiz = self::getInstance();
		$data = array();
		$productId = self::urlVar($url,'ProductID');

		$data[$productIdMap[$productId]]= 'True';
		$data['ProductId'] = $productId;
		$data2 =array_flatten(xml2array($html),array(),false);
		if (isset($data2[0]))
		{
			$data = array_merge($data,$data2[0]);
			
			$data = db::normalize($data);

			if (!empty($data['DISPLAYEDNAME']))
			{
				log::info($data);
				unset($data['ID']);
				
				db::store($type,$data,array('TELEPHONENUMBER','DISPLAYEDNAME','PRODUCTID'));	
			}
		}

	}
}
$type = 'botox';	
db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
$r= new botox();
$r->parseCommandLine();

