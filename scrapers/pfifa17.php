<?
include_once "config.inc";
//R::freeze();

class pfifa17 extends baseScrape
{
    public static $_this=null;
	public $workChunkSize = 100;
   public function runLoader()
   {
		$type = get_class();		
		$this->threads=1;		
		
		// this isn't even a scrape have to login to thier site and manually grab the ajax requests directly.
		
		$json = file_get_contents("pfifa17.json");
		$datas = json_decode($json,true);
		//log::info($json);
		// log::info($datas);
		for ($i=0;$i<sizeof($datas);$i++)
		{
			
			foreach ($datas[$i]['attendees'] as $allData)
			{
				$profile = $allData['profile'];
				if (isset($profile['address']))
				{
					$address = $profile['address'];
					unset($profile['address']);
					$data = array_merge($profile,$address);
				}
				else
					$data = $profile;
				
				$data['CONTACT_ID'] = $allData['contact_id'];
				
				
				log::info($data['first_name']);
				db::store($type,$profile,array('FIRST_NAME','LAST_NAME','EMAIL','ADDRESS'),true);			
			}
		}
	}

}

$r= new pfifa17();
$r->parseCommandLine();

