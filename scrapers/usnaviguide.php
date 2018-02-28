<?
include_once "config.inc";

class usnaviguide extends baseScrape
{
   public static $_this=null;

	public function runLoader()
   {
		
		$type = get_class();		
		//db::query("DELETE FROM load_queue where type='$type' ");
		#db::query("UPDATE raw_data set parsed = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type' ");
		#db::query("DELETE FROM $type");
				
		$this->threads=1;

		$this->debug=false;

		$this->loadUrlsByZip("http://ts1.usnaviguide.com/imageserver.pl?M=M&Z=Z&K=O&N=%ZIP%&T=fc80ec7dec",0);

   }
	
	static function LoadCallback($url,$html)
	{
		sleep(1); // don't slurp too fast
		parent::LoadCallback($url,$html);

	}
	static function parse($url,$html)
	{
		$type = get_class();		
		$thiz = self::getInstance();
		
		$zip = $thiz->urlVar($url,"N");
		$kbytes = round(strlen($html)/1024);
		log::info("Saving $zip.png - $kbytes Kb");
		file_put_contents("d:/dev/zipcodeimages/$zip.png",$html);
	}

}


$r= new usnaviguide();
$r->parseCommandLine();