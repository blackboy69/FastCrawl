<?
include_once "config.inc";
include_once "search_engine_yahoo_local.php";

class padental extends baseScrape
{
    public static $_this=null;
	
   public function runLoader()
   {
		$type = get_class();		
		$this->noProxy=true;
//		$this->proxy = "localhost:8888";
		$this->allowRedirects = false;
		$this->threads=4;
		$this->debug=false;
		
		//db::query("DELETE FROM load_queue where type='$type'");
		/*
		db::query("UPDATE load_queue set processing = 0 where type='$type' and processing = 1 ");
		db::query("UPDATE raw_data set parsed = 0 where type='$type' and parsed = 1  ");
		db::query("Drop table $type");
		*/


		$url = "http://www.padental.org/Scriptcontent/VA_Custom/Directories/DirectoryOutput.cfm?Section=Find_a_Member_Dentist";
		$data = "txtSection=Find+a+Dentist&txtName=&txtCompany=&txtZip=&txtCity=&selState=PA&selCounty=&selSpecialty=&selRecsToShow=25&submit=Search";
		$this->loadPostUrl($url,$data);

	}

	public static function parse($url,$html)
	{
		$query = array();
		$type = get_class();		
		$thiz = self::getInstance();		
		$host = parse_url($url,PHP_URL_HOST);
		$ap = new Address_Parser();
		$kvp = new KeyValue_Parser();

		parse_str(parse_url($url,PHP_URL_QUERY),$query); // address and zip	
		
		$x = new Xpath($html);
		try
		{	
			foreach($x->query("//div[contains(@id,'recordDetail')]//fieldset") as $node)
			{
				$data = array();
				$x2 = new Xpath($node);
				foreach($x2->query("//legend") as $node2)
				{
					$data['NAME'] = $node2->textContent;
				}
				
				$content = explode("<br>", preg_replace("/<legend.+legend>/", "", $x2->html));

				$data['BUSINESS NAME'] = strip_tags(trim($content[0]));
				$data = array_merge($data, $ap->parse(array_slice($content, 1, 2)));
				$data = array_merge($data, $kvp->parse(array_slice($content, 3)));
								
				$data['SOURCEURL'] = $url;
				db::store($type,$data,array('NAME','ADDRESS','CITY'));
			}
			// grab any next page links
			foreach($x->query("//a[contains(@href,'startrow=')]") as $node)
			{
				$href = $thiz->relative2absolute($url,$node->getAttribute("href"));
				parse_str(parse_url($href,PHP_URL_QUERY),$hrefQuery); // address and zip	
				$startrow = $hrefQuery['startrow'];
				$u = "http://www.padental.org/Scriptcontent/VA_Custom/Directories/DirectoryOutput.cfm?Section=Find_a_Member_Dentist&startrow=$startrow";
				$d = "txtSection=Find+a+Dentist&txtName=&txtCompany=&txtZip=&txtCity=&selState=PA&selCounty=&selSpecialty=&selRecsToShow=25&submit=Search&startrow=$startrow";
				$thiz->loadPostUrl($u,$d);
			}
		}
		catch(Exception $e)
		{

			log::error ("Cannot store ".$data['NAME']);
			log::error($e);
			print_R($data);
			exit;
		}		
	}
}

$r= new padental();
$r->parseCommandLine();

