
<?
include_once "config.inc";

class one800Dentist extends baseScrape
{
   public static $_this=null;

   // this function is threadsafe and can be run concurrently
   public function runLoader()
   {
      one800Dentist::$_this = $this;

      // because the static callback needs to call our parse data function.
      //db::query("DELETE FROM load_queue where type='one800Dentist' ");//and url like '%Next%'");
      //  db::query("DELETE FROM load_queue");
  //    db::query("DELETE FROM raw_data ");
      //db::query("DELETE FROM one800Dentist");
      
//       db::query("DELETE FROM load_queue  where type='one800Dentist' and url='http://www.1800Dentist.com'");
      
      $this->threads=1;
      $this->useCookies=true;
      $this->debug=false;
      $this->allowRedirects = true;
      $this->timeout = 30;
      //$this->proxy = "localhost:8888";


   $choices[] ="1027"; //braces
   $choices[] ="1011"; //dental implants
   $choices[] ="1009"; //gum disease
   $choices[] ="1021"; //Root canal

/*   $choices[] ="1010";
   $choices[] ="1006";
   $choices[] ="1007";
   $choices[] ="1011";
   $choices[] ="1012";
   $choices[] ="1002";
   $choices[] ="1009";
   $choices[] ="1021";
   $choices[] ="2002";
   $choices[] ="6";
   */
//   $choices[] ="1024";
/*   $choices[] ="1004";
*/
//   $choices[] ="8";
//   $choices[] ="1025";
//      $choices[] ="1002";

	
      $this->loadUrl("http://www.1800Dentist.com");
      // this one will load a bunch of urls to process
      $this->queuedGet('one800Dentist::loginCallBack');

      $data = $this->loginData;
      $result = mysql_query("SELECT distinct zip FROM geo.locations where pop > 25000");
      while ($r = mysql_fetch_row($result))
      {
         $zip = trim($r[0]);
         
         foreach ($choices as $choice)
         {
            $data['ctl00$cphMain$sZipCode']=$zip;
            $data['ctl00$cphMain$iChoice']=$choice;
            $data['ctl00$cphMain$iChildAge']=-1;
            $data['ctl00$cphMain$rbgDentalInsurance']='rbCash';
            $data['ctl00$cphMain$ImageButton1.x']=rand(0,131);
            $data['ctl00$cphMain$ImageButton1.y']=rand(0,11);
            $data['ctl00$cphMain$hdnPaymentType']='none';
            $this->setReferer("*", "http://www.1800Dentist.com");

            $query = http_build_query($data,'','&');
            $loadUrl = "http://www.1800dentist.com?$query";
            log::info( "Loading $choice $zip..." );
            
            $this->toLoadFinal = "";

            $this->loadUrl($loadUrl);
            $this->queuedPost('one800Dentist::refererCallBack');
            
            $this->pages = 1;
            
            $parseCallback = "one800Dentist::parseData";
/*            if ($choice == 8) {
               $parseCallback = "one800Dentist::parseDataSpecialist";
            }*/

            $this->loadUrl("$this->toLoadFinal&zip=$zip&choice=$choice");
            $this->queuedGet($parseCallback); 
            
            for ($i=2;$i<=$this->pages;$i++)
            {
               $this->loadUrl("$this->toLoadFinal&zip=$zip&choice=$choice&page=$i");
               $this->queuedGet($parseCallback);
            }
         }
      }
   }
   

   public static function loginCallBack($url,$html,$type)
   {
      // do nothing
      log::info("Logging in... ");

      # lets scrape all the hidden fields
      # 
      $dom = new DOMDocument();
      // for some strange reason, php dom turns non breaking spaces into this  ┬а
      @$dom->loadHTML($html);
      $x = new DOMXPath($dom);
      $data = array();

      foreach($x->query("//input[@type='hidden']") as $node)
      {
         $name = $node->getAttribute('name');
         $value = $node->getAttribute('value');

         if (trim($name) == 'l')
            continue;
         
         if (empty($name))
            continue;

         $data[$name] = $value;
      }

      one800Dentist::$_this->loginData = $data;
   }

   public static function refererCallBack($url,$html,$type)
   {

      if (strlen($url) < 10)
      {
         log::info("somthing bad happend. only got $url");
         return;
      }
      parse_str(parse_url($url,PHP_URL_QUERY));

      // no need to parse, just use a regex
      if (preg_match("/location.href='([^']+)';/", $html,$matches))
      {
         log::info ("Forwarding...$url");
         if (! strstr($matches[1],'DN=1655eb1a-0be5-4a48-96eb-ec624de451f9'))
         {
            one800Dentist::$_this->toLoadFinal = $matches[1];       
         }
         else
         {
            log::info("no-dental-offices-found");
         }
      }
      else
      {
         log::info("Could not forward $url");
      }
   }

   public function parseData($url,$html,$type)
   {
      
      log::info("Parsing $url...");
      // lets do this with the built in dom instead of simple_dom_html....
      $dom = new DOMDocument();
      @$dom->loadHTML($html);
      $x = new DOMXPath($dom);
      
      $data = array();
     //Practice Name, City, State, Zip, and Affiliations
      
      foreach($x->query("//table[@id='ctl00_cphMain_DentistList1_dlSearchResult']/tr/td/table/tr[@style='vertical-align:top;']") as $node)
      {
         $dom2 = new DOMDocument();
         @$dom2->loadHTML($node->c14n());
         $x2 = new DOMXPath($dom2);
         $data = array();

         foreach($x2->query("//td[3]/div[1]") as $node2)
         {
            $data['name']=$node2->textContent;
            break;
         }

         foreach($x2->query("//td[3]/div[2]") as $node2)
         {
            $data['phone']=$node2->textContent;
            break;
         }
         foreach($x2->query("//td[3]/div[3]") as $node2)
         {
            $location=$node2->textContent;
            list($address,$rest) = explode("<br>",$node2->c14n());
            list($city,$rest) = explode(",",$rest);

            list($state,$zip) = explode(" ",trim($rest));

            $data['address'] =  strip_tags($address);
            $data['city'] = strip_tags($city);
            $data['state'] = strip_tags($state);
            $data['zip'] = strip_tags($zip);

            break;
         }
         $affiliations = array();
         foreach($x2->query("//td[9]/table/tr/td/span/table/tr/td/img") as $node2)
         {
            $affiliations[] = $node2->getAttribute("alt");
         }

         $data['affiliations'] = join(", ",$affiliations);

         log::info("{$data['name']} {$data['state']}");
         db::replaceInto($type,$data);
      }      
      // uncomment when done testing :D
      db::query("UPDATE raw_data set parsed = 1 where type='$type' and url = '$url'");
   
      
      // check to see if there is a next link...
      foreach( $x->query("//span[@id='ctl00_cphMain_lblBottomPager']/*/a[last()]") as $node)
      {
         log::info("Found $node->textContent pages.");
         one800Dentist::$_this->pages = $node->textContent;
      }
   }  

}

$r = new one800Dentist();
$r->runLoader();
#$r->generateCSV();