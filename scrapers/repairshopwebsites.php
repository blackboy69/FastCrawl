<?
include_once "config.inc";

class repairshopwebsites extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		$type = get_class();		
		$this->threads=1;
		$this->debug=false;
		db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		//$this->loadUrl("http://www.repairshopwebsites.com/rsw/locator.html?state=MI");
		$this->loadUrlsByState("http://www.repairshopwebsites.com/rsw/locator.html?state=%STATE%");

   }

	static function parse($url,$html)
	{
		$type = get_class();		
		
		$html = preg_replace("/<br.?\/>/","|",$html);
		$html = preg_replace("/<br>/","|",$html);
		$html = preg_replace("/(\t|\n|\r)+/"," ",$html);
		
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$x = new DOMXPath($dom);	

		foreach ($x->query("//table[@class='servicesT']/tr/td") as $node)
		{

			$data = array();

			@list($data['name'],$data['address'],$cityStateZip,$data['phone'],$data['url']) = explode("|", trim($node->textContent));
			if (preg_match("/(.+), ([A-Z][A-Z]) ([0-9]{5})/", $cityStateZip,$matches))
			{
				$data['city'] = $matches[1];
				$data['state'] = $matches[2];
				$data['zip'] = $matches[3];			

				if (isset($data['name']) && isset($data['phone']))
				{
					log::info($data['zip'] . $data['state'] ." - " . $data['name'] );
					db::store($type, $data,array('name','phone'),false);
				}
			}
		}
	}	

}
global $nomatch;
$nomatch = array();
$r = new repairshopwebsites();

//for($i=0;$i<5;$i++)
{
	$r->runLoader();
	$r->parseData();
}

$r->generateCSV();