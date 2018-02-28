<?

include_once "config.inc";

class napa extends baseScrape
{
   // this function is threadsafe and can be run concurrently
   public function runLoader()
   {
      # generate a list of urls to parse
      $this->expireQueue(1); // expire urls that are older than 1 days
/*
      db::query("DELETE FROM load_queue WHERE type = 'napa'");
      db::query("DELETE FROM raw_data WHERE type = 'napa'");
      db::query("DELETE FROM napa");
*/
      $urls = file('napa_urls.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $num_urls = $this->loadUrlsByArray($urls);
      //$this->loadUrl("http://www.napalocator.com/storeinfoUI.aspx?sid=100090");

      $num_to_process = $this->numToProcess();
      log::info("processing $num_to_process");

//      $this->debug=true;
      $this->threads=10;
      $this->useCookes=false;
      $this->queuedPost();
   }


   public function parseData()
   {
      
      $type =get_class($this);
      
      // right now we have only listing pages loaded in the queue.
      // parseListings will load detail data into queue also
      // so we use a forever loop to check the raw_data  (the results of the load queue) to see if there is any more data left to parse
      for (;;)
      {
         // now we just iterate raw_data, pull out what we want and stick in the correct table.
         $sql = "SELECT url,html FROM raw_data WHERE type = '$type' and parsed = 0 LIMIT 1";

         $row = db::query($sql);

         $url = $row['url'];
         $html = $row['html'];
         log::info("parsing $url");

         if (empty($url))
         {
            break;
         }
         // lets do this with the built in dom instead of simple_dom_html....
         $dom = new DOMDocument();
         @$dom->loadHTML($html);
         $x = new DOMXPath($dom);
         
         $data = array();
         foreach($x->query("//span[@id='ctl00_MidContent_lblFacilityName']") as $node) 
         {
            $data['shopName'] = $node->textContent;
         }
         foreach($x->query("//span[@id='ctl00_MidContent_lblAddressPart1']") as $node) 
         {
            $data['address1'] = $node->textContent;
         }
         foreach($x->query("//span[@id='ctl00_MidContent_lblAddressPart2']") as $node) 
         {
            $data['address2'] = $node->textContent;
         }

         foreach($x->query("//span[@id='ctl00_MidContent_lblPhone']") as $node) 
         {
            $data['phone'] = $node->textContent;
         }

         foreach($x->query("//span[@id='ctl00_MidContent_lblFax']") as $node) 
         {
            $data['fax'] = $node->textContent;
         }

         foreach($x->query("//span[@id='ctl00_MidContent_lblTowingPhone']") as $node) 
         {
            $data['towPhone'] = $node->textContent;
         }

         foreach($x->query("//a[id='ctl00_MidContent_hlEmail']") as $node) 
         {
            $data['email'] = $node->textContent;
         }

         foreach($x->query("//span[@id='ctl00_MidContent_hlWebsite']") as $node) 
         {
            $data['website'] = $node->textContent;
         }
         
         foreach($x->query("//span[@id='ctl00_MidContent_lblOwnerName']") as $node) 
         {
            $data['owner'] = $node->textContent;
         }

         foreach($x->query("//span[@id='ctl00_MidContent_lblManagerName']") as $node) 
         {
            $data['manager'] = $node->textContent;
         }

         foreach($x->query("//span[@id='ctl00_MidContent_lblMasterTechExp']") as $node) 
         {
            $data['masterTechExp'] = $node->textContent;
         }

         foreach($x->query("//span[@id='ctl00_MidContent_lblYIB']") as $node) 
         {
            $data['yearsInBusiness'] = $node->textContent;
         }

         foreach($x->query("//span[@id='ctl00_MidContent_lblNumberTechnicians']") as $node) 
         {
            $data['numTechs'] = $node->textContent;
         }

         foreach($x->query("//span[@id='ctl00_MidContent_lblAverageExp']") as $node) 
         {
            $data['avgExp'] = $node->textContent;
         }         
         $data['url'] = $url;
         
         db::replaceInto($type,$data);
      
         // uncomment when done testing :D
         db::query("UPDATE raw_data set parsed = 1 where type='$type' and url = '$url'");
      }
   }
}
$r = new napa();
$r->runLoader();
$r->parseData();
$r->generateCSV();
