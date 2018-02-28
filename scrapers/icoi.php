<?
include_once "config.inc";

class icoi extends baseScrape
{
   public static $_this=null;

   // this function is threadsafe and can be run concurrently
   public function runLoader()
   {
      icoi::$_this = $this;

      // because the static callback needs to call our parse data function.
      //db::query("DELETE FROM load_queue where type='icoi' ");//and url like '%Next%'");

      $this->loadUrl("http://www.icoi.org/script-SearchResults.asp?countries=Canada");
      $this->loadUrlsByState("http://www.icoi.org/script-SearchResults.asp?states=%STATE%",'US');
      
      $this->threads=1;
      $this->useCookies=true;
      $this->debug=false;
      $this->allowRedirects = true;

      // this will populate the raw_data table with the html and type=icoi
      $this->queuedPost('icoi::parseData');

   }

   public static function loginCallBack($url,$html,$type)
   {
      // do nothing
      log::info("Logging in...");
   }


   // THIS FUNCTION IS NOT THREAD SAFE!!!!!
   public static function parseData($url,$html,$type)
   {
      
// we have to process this stuff sequentially because of the session paging on this site....      

      $dom = new DOMDocument();
      // for some strange reason, php dom turns non breaking spaces into this  ┬а
      @$dom->loadHTML(str_replace("&nbsp;", " ", $html));
      $x = new DOMXPath($dom);

      log::info ("Parsing $url");
      $done = false;
      $error =false;
      $step = 0;
      
      $name=$office=$address=$city=$state=$zipcode=$country=$phone=$fax=$email=$website=$speciality=$level=$page="";
      foreach( $x->query("/html/body/table/tr[5]/td/blockquote/table/tr/td/table/tr") as $node)
      {
         $htmlFragment = $node->c14n();
//echo "\n\n$htmlFragment\n\n";
         $c = trim($node->textContent);

         if (strstr($htmlFragment,"No Results"))
         {
            break;
         }
         if (strstr($htmlFragment,"<hr>"))
         {
            $done  = true;
          
         }
         if ( strstr($htmlFragment,"<b>") )
         {

            // this is the dentist name
            $name = db::quote($c);
            $step = 1;
            //log::info("Name: $name");
         }
         else if ($step == 1  && ! preg_match("/^#?[0-9]/",$c))
         {
            // this is the practice name
            $office = db::quote($c);
            //log::info("Office: $office");
         }
         else if ($step == 1 && preg_match("/^#?[0-9]/",$c))
         {
            // looks like an address. assumes all addresses start with a number
            $address = db::quote($c);
            $step = 2;
            //log::info("Address: $address");
         }
         else if ($step == 2 && preg_match("/^Suite /i",$c))
         {
            $address .= " $c";
            //log::info("Suite: $c");
         }
         else if ($step == 2 && preg_match("/([A-Za-z ]+) ([A-Z][A-Z]) ([0-9\-]+)/",$c,$matches) )
         {
            // US city state zip
            $city    = db::quote($matches[1]);
            $state   = db::quote($matches[2]);
            $zipcode = db::quote($matches[3]);
            $country = 'USA';
            $step = 3;
            //log::info("Location: $city,$state,$zipcode,$country");
         }
         else if ($step == 2 && preg_match("/([A-Za-z \-']+) ([A-Z][A-Z]) (.+)/",$c,$matches) )
         {
            // CANADIAN
            $city    = db::quote($matches[1]);
            $state   = db::quote($matches[2]);
            $zipcode = db::quote($matches[3]);
            $country = 'CANADA';
            $step = 3;
            //log::info("Location: $city,$state,$zipcode,$country");
         }
         else if ($step==3 && preg_match("/^Phone:(.*)/",$c,$matches))
         {
            $phone = db::quote($matches[1]);
            //log::info("Phone: $phone");
         }
         else if ($step==3 && preg_match("/^Fax:(.*)/",$c,$matches))
         {
            $fax = db::quote($matches[1]);
            //log::info("Fax: $fax");
         }
         else if ($step==3 && preg_match("/^Email:(.*)/",$c,$matches))
         {
            $email = db::quote($matches[1]);
            //log::info("Email: $email");
         }
         else if ($step==3 && preg_match("/^Website:(.*)/",$c,$matches))
         {
            $website = db::quote($matches[1]);
            //log::info("Website: $website");
         }
         else if ($step==3 && preg_match("/^Specialty:(.*)/",$c,$matches))
         {
            $specialty = db::quote($matches[1]);
            //log::info("Specialty: $specialty");
         }
         else if ($step==3 && preg_match("/Recognition Level:(.*)/",$c,$matches))
         {
            $level = db::quote($matches[1]);
            //log::info("Level: $level");
         }
         else if ($c == 'Canada')
         {
            // okay we know about canada
         }
         else if(strstr($c, 'Total Records'))
         {
            log::info($c);
         }
         else if(preg_match('/Next|Previous/',$c))
         {

         }
         else if(preg_match('/([0-9]{1,4}) - [0-9]{1,4}/',$c,$matches))
         {
            // paging records
            $page = $matches[1];
         }
         else if (strlen ($c))
         {
           
           log::error("unknown row $c");
           //lets just tack on to address cause thats what is probably is.
           $address .= " $c";
           
         }
         if($done)   
         {
            //log::info("$name\t$office\t$address\t$city\t$state\t$zipcode\t$country\t$phone\t$fax\t$email\t$website\t$speciality\t$level");
            if ($done)
            {
               $sql = "
                     REPLACE INTO icoi (name, office, address, city, state, zipcode, country,phone,fax, email, website, speciality,level)
                       VALUES('$name','$office','$address','$city','$state','$zipcode','$country','$phone','$fax','$email','$website','$speciality','$level')
               ";

               db::query($sql);
               $done=false;
               $name=$office=$address=$city=$state=$zipcode=$country=$phone=$fax=$email=$website=$speciality=$level="";
            }
         }
         
      }
      
      // check to see if there is a next link...
      foreach( $x->query("/html/body/table/tr[5]/td/blockquote/table/tr/td/table/tr[last()]/td/a[last()]") as $node)
      {
         log::info("Going to page $page");
         icoi::$_this->loadUrl("http://www.icoi.org/".$node->getAttribute("href")."&page=$page");
         icoi::$_this->queuedPost("icoi::parseData");
      }
   }   
}

$r = new icoi();
$r->runLoader();
$r->generateCSV();