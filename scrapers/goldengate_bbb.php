<?php

include_once "config.inc";

class goldengate_bbb extends baseScrape
{
   public static $_this=null;
	public static $numCalls = 0;
	public $proxies = array();

	public function runLoader()
   {
		
		$type = get_class();		

		// db::query("DELETE FROM load_queue where type='$type'");
		//db::query("UPDATE raw_data set parsed = 0 where type='$type' ");

		//db::query("UPDATE load_queue set processing = 0 where type='$type' ");
		//db::query("DELETE FROM raw_data where type='$type'");
		#db::query("DROP TABLE  $type");
		//db::query("DELETE FROM $type");

		
		$this->threads=6;
		$this->noProxy=false;
		//$this->proxy = "localhost:9666";
//		$this->proxy = "localhost:8888";

		$this->timeout = 15;
		$this->debug=false;	/*
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/matched/salon/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/matched/Day+Spas/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/matched/Nail+Salons/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/salon/tanning-salons/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/salon/beauty-salons/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/beauty+school/beauty-school/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/barber/barber-schools/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/barber/barbers/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/massage/massage-therapists/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/medical+spa/hair-styling-and-services/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/medical+spa/health-resorts/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/name/Plastic+surgery/%ZIP%");


		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/auto/auto-body-repair-and-painting/%ZIP%");
		$this->loadUrlsByZip("http://goldengate.bbb.org/Find-Business-Reviews/type/auto/auto-lube-and-oil-mobile/%ZIP%");
		db::query("		
		UPDATE load_queue
		 
		 SET processing = 0

		 WHERE			 
			 url NOT IN (SELECT url FROM raw_data WHERE type ='$type')
			 AND type ='$type'
		");
		
		
		db::query("

		UPDATE load_queue
		 
		 SET processing = 1

		 WHERE
			 url IN (SELECT url FROM raw_data WHERE type ='$type')
			 AND type ='$type'
		");*/
//	exit;
		
		//$this->loadUrl("http://www.bbb.org/greater-san-francisco/business-reviews/auto-repair-and-service/autometrics-auto-repair-in-berkeley-ca-10210"); //%ZIP%


   }

   public static function parse($url,$html){}
	
   // basic load call back to populate the db.
   public static function loadCallBack($url,$html,$type)
   {
	   static $numCalls = 1;

	   if ($numCalls > 50)
	   {
		   $numCalls=0;
		   self::getInstance()->switchProxy($url);
	   }

	   if (strpos($html,"Sorry, we had to limit your access to this website.") || strlen($html) <  4000)
	   {
		   self::getInstance()->switchProxy($url);
	   }
	   parent::loadCallBack($url,$html,$type);
   }

}
$r = new goldengate_bbb();

$r->runLoader();
$r->parseData();

$r->generateCSV(true,false,false," WHERE category IN ('Hypnotherapists', 'Podiatrists', 'Exercise fitness programs', 'Physicians and surgeons family practice', 'Nutritionists', 'Health and wellness', 'Acupressure', 'Vitamins and food supplements', 'Training programs', 'Alternative medicine', 'Hair replacement product suppliers', 'Dry cleaners', 'Estheticians', 'Business services general', 'Antiques dealers', 'Wedding consultants', 'Personal trainers', 'Schools massage therapy', 'Pet grooming', 'Hair weaving', 'Cosmetic sales', 'Occupational therapists', 'Gymnasiums', 'Spas beauty', 'Cosmetic laser', 'Hypnotherapists hypnotists', 'Gift shops', 'Salon products and equipment', 'Bridal shops', 'Physicians and surgeons osteopathic do', 'Physicians and surgeons plastic and reconstructive surgery', 'Schools general interest', 'Convenience stores', 'Laundries', 'Department discount outlet stores', 'Massage equipment and supplies', 'Physicians surgeons', 'Colon hydrotherapy colonic therapy', 'Barber and beauty schools', 'Naturopaths', 'Dentists', 'Chiropractic doctors and clinics', 'Lawn and garden equipment and supplies', 'Travel agencies and bureaus', 'Cosmetic surgeons', 'Schools academic colleges and universities', 'Permanent make up', 'Physicians and surgeons medical doctors', 'Physicians and surgeons', 'Schools beauty', 'Bed and breakfast', 'Rehabilitation services', 'Day spa salons', 'Laser cosmetic services', 'Physicians and medical clinics', 'Restaurants', 'Barber shops', 'Braids', 'Hair removing', 'Tanning salons equipment and supplies', 'Discount stores', 'Tattoos', 'Skin care suppliers', 'Health and diet products retail', 'Boutiques', 'Beauty salon full service', 'General merchandise retail', 'Cosmetic plastic and reconstructive surgery practices', 'Signs', 'Beauty consultant', 'Specialist physicians', 'Massage school', 'Wigs and hairpieces', 'Plastic surgery offices', 'Yoga instruction', 'Hair salons and spas', 'Manicuring', 'Hair removal laser electrolysis', 'Health and fitness program consultants', 'Hair removal service', 'Plastic and reconstructive surgery', 'Hair cutting and styling', 'Cosmetic plastic and reconstructive surgery', 'Physicians and surgeons cosmetic plastic and rec', 'Surgical centers', 'Video tapes and discs sales and rentals', 'Fitness centers', 'Personal services', 'Resorts', 'Laser hair removal companies', 'Cosmetics and perfumes retail', 'Health and beauty spas', 'Recreational vehicles repair and service', 'Day spa', 'Spas day spas', 'Therapeutic massage spas', 'Weight control services', 'Hair removal laser and electrolysis', 'Clinics', 'Facials and skin care spas and salons', 'Not elsewhere classified', 'Fingernail salons', 'Holistic practitioners', 'Hotels', 'Physicians and surgeons dermatology', 'Hair salons', 'Electrolysis', 'Physicians and surgeons hormone replacement therapy', 'Beauty supplies and equipment', 'Acupuncturists', 'Hair replacement', 'Spas day', 'Therapeutic massage', 'Exercise and physical fitness programs', 'Schools business and vocational', 'Health and medical general', 'Physical therapists', 'Physicians specialists', 'Massage therapeutic', 'Massage therapy services and supplies', 'Beauty schools', 'Health clubs', 'Barber schools', 'Physicians and surgeons medical md', 'Cosmetology and beauty salon services', 'Skin care', 'Health resorts', 'Physicians and surgeons cosmetic plastic and reconstructive surgery', 'Chiropractors dc', 'Tanning bed salons', 'Barber beauty shops', 'Hair styling and services', 'Beauty salons treatments', 'Day spas', 'Massage therapists', 'Barbers', 'Nail salons', 'Tanning salons', 'Beauty salons') ");

