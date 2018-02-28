<?
include_once "config.inc";
include_once "HtmlParser.php";

// this site has also has the www.oda.ca domain

class youroralhealth extends baseScrape
{
	public function runLoader()
   {

		db::query("DELETE FROM youroralhealth");
		
		$xml = file_get_contents("youroralhealth.xml");

		$doc = new DOMDocument();
		$doc->loadXml($xml);

		$x = new DOMXpath($doc);
		
		foreach($x->query("//marker") as $node)
		{			
			$data['name'] = $node->getAttribute("Name");
			$data['specialty'] = $node->getAttribute("specialty");
			$data['other_address'] = $node->getAttribute("add_to_send");
			
			$data['address'] = $node->getAttribute("address");		
			
			if (preg_match("/(.+)<br> ([0-9]{3}-[0-9]{3}-[0-9]{4})<br>/",$data['address'],$matches))
			{
				$data['address']  = $matches[1];			
				$data['phone']		= $matches[2];			
			}
			
			log::info($data['name']);

			db::store("youroralhealth", $data );
		}
   }	

}
$r = new youroralhealth();
$r->runLoader();
$r->generateCSV();
