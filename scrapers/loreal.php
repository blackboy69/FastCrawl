<?
include_once "config.inc";


class loreal extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		

	#	$this->noProxy=false;
	#	$this->proxy='localhost:8888';

#		db::query("DELETE FROM load_queue  where type='$type' ");
//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");




		$this->loadUrlsByCity("http://hosted.where2getit.com/lorealprofessionnelus/ajax?&xml_request=".urlencode('<request><appkey>56CB9E62-4910-11E1-854F-4F1A58283E85</appkey><formdata id="locatorsearch"><dataview>store_default</dataview><softmatch>1</softmatch><limit>250</limit><geolocs><geoloc><addressline>')."%CITY%,+%STATE%".urlencode('</addressline><longitude></longitude><latitude></latitude></geoloc></geolocs><searchradius>25|50|100|250</searchradius><where><name><ilike></ilike></name></where></formdata></request>'));
		
   }


	static function parse($url,$xml)
	{
		
		$type = get_class();		

		$xTop = new Xpath($xml);		
		foreach ($xTop->query("//poi") as $nodeListing)


		{

			$x = new Xpath($nodeListing);
			$dataIn =xml2array( $nodeListing->c14n());
			$data = db::normalize($dataIn['poi']);
			$data['SOURCE_URL'] = $url;

			if (array_key_exists('UID', $data))
			{
				log::info($data);

				db::store($type, $data,array('UID'),false);
			}
		}
	}


}
global $nomatch;
$nomatch = array();
$r = new loreal();
$r->ParseCommandLine();

print_r($nomatch);