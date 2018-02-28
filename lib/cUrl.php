<?
/*
	Byron Whitlock
	byron@gmail.com
	2009-04-25
	www.linkedin.com/in/byronwhitlock

	This class is for screen scraping
 */
 
class cURL
{

	// this must be in a writeable location.
	var $cookie_file = "cookies.txt";
	public $debug = false;
   var $headers;
	
	public function __construct($u="",$p="")
	{
		$this->username = $u;
		$this->password = $p;
		$this->ch = curl_init();
	#	$this->removeCookies();
	}
	public function removeCookies()
	{
		if (is_file($this->cookie_file))
		{
			unlink($this->cookie_file);
		}
	}

	function Get($url)
	{
		return $this->Post($url);
	}

	function Post($url,$data = "")
	{


      // if data is a url, treat as post
      if (preg_match("/^http/",$data))
      {
         $actualURL = $data;
         $data = NULL;
      }
      else
      {
         $actualURL = $url;
      }
      
      # remove any trailing # signs, but don't take ? query params out
      $actualURL = preg_replace("/\#.+?\???/","",$actualURL);

      $this->ch = curl_init($actualURL);

		$this->location = "";
		$ch = $this->ch;
		$this->error = "";

		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
      curl_setopt($ch, CURLOPT_COOKIESESSION,true);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); 

		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.23 (Windows NT 5.1; U; en)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects 

		## Below two option will enable the HTTPS option.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);


		// set this to true and run on the command line to see the debug output. it isn't really usefull
		if ($this->debug)
		{
			curl_setopt($ch, CURLOPT_VERBOSE, true); 
		}



		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
		curl_setopt($ch, CURLOPT_PROXY, 'localhost:8888'); 

		//register a callback function which will process the headers
		//this assumes your code is into a class method, and uses $this->readHeader as the callback //function
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'readHeader'));


		if ($data)
		{
			curl_setopt($ch, CURLOPT_POST, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
		}

		$result = curl_exec($ch);
		

		if (!$result)
		{
			$this->error = curl_error($ch);
		}
		return $result;	
	}

	// does multi processing
	// urlData is an array in the form:
	// array( 
	//    URL => urldata,
	//    URL => urldata,
	//    URL => urldata
	// )
	// 
	// returns a result in the form
	//  array(
	//     URL => html,
	//     URL => html,
	//     URL => html
	//  )
	public function PostMulti($urlData)
	{
		$master = curl_multi_init();

		foreach ($urlData as $url => $data )
		{

			// if data is a url, treat as post
			if (ereg("^http",$data))
			{
				$actualURL = $data;
				$data = NULL;
			}
			else
			{
				$actualURL = $url;
			}
			
         # remove any trailing # signs, but don't take ? query params out
         $actualURL = preg_replace("/\#.+?\???/","",$actualURL);

			$curl[$url] = curl_init($actualURL);
			curl_multi_add_handle($master, $curl[$url]);

			curl_setopt($curl[$url], CURLOPT_AUTOREFERER, true);
			curl_setopt($curl[$url], CURLOPT_COOKIEFILE, $this->cookie_file);
			curl_setopt($curl[$url], CURLOPT_COOKIEJAR, $this->cookie_file); 

			curl_setopt($curl[$url], CURLOPT_URL,$actualURL);
			curl_setopt($curl[$url], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.19) Gecko/2010031422 Firefox/3.0.19 ( .NET CLR 3.5.30729; .NET4.0E)');
			curl_setopt($curl[$url], CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curl[$url], CURLOPT_FOLLOWLOCATION, 1);// allow redirects 
			curl_setopt($curl[$url], CURLOPT_TIMEOUT, 15); //timeout after 15 seconds 

			## Below two option will enable the HTTPS option.
         if (preg_match("/^https/",$url))
         {
   			curl_setopt($curl[$url], CURLOPT_SSL_VERIFYPEER, FALSE);
	   		curl_setopt($curl[$url], CURLOPT_SSL_VERIFYHOST,  2);
         }
			if ($data)
			{
				curl_setopt($curl[$url], CURLOPT_POST, 1); 
				curl_setopt($curl[$url], CURLOPT_POSTFIELDS, $data); 
			}
			// set this to true and run on the command line to see the debug output. it isn't really usefull
			if ($this->debug)
			{
				curl_setopt($curl[$url], CURLOPT_VERBOSE, true); 
			}

		}

		do {
			curl_multi_exec($master,$running);
		} while($running > 0);

      $results = array();
		foreach ($urlData as $url => $data )
		{
			$results[$url] = curl_multi_getcontent  ( $curl[$url]  );
			curl_multi_remove_handle( $master, $curl[$url] );
			
		}
		curl_multi_close ( $master  );			
		return $results;
	}
	// does multi processing based on a pool of urls. 
	// will iterate the pool of urls $concurrent urls at a time.
	//
	// urls is a simple array of urls. they should be unique.
   // urlData is an array in the form:
	// array( 
	//    URL => urldata,
	//    URL => urldata,
	//    URL => urldata
	// )
	// readCallback is a function that gets called everytime a url is read, the signature is 
	//  - readCallback($url,$html)
	//
	// threads number of urls to process concurrently.
	// timeout how long to wait on each thread.

	public function queuedPost($urlData,$callback, $threads,$timeout,$proxy=false,$useCookies=true)
	{
		$mcurl = curl_multi_init();
		$threadsRunning = 0;
		$urls_id = 0;
		$curlMap = array();
      
      $urls = array_keys($urlData);

		for(;;) 
		{
			// Fill up the slots
			while ($threadsRunning < $threads && $urls_id < count($urls)) 
			{

            $url = $urls[$urls_id++];
            $data = $urlData[$url];
            
            # remove any trailing # signs, but don't take ? query params out
            $actualURL = preg_replace("/\#.+\???/","",$url);

				$ch = curl_init();
            
            if ($proxy != false)
            {
         		#curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
               curl_setopt($ch, CURLOPT_PROXY, $proxy); 
            }


            if ($useCookies)
            {
#               curl_setopt($ch, CURLOPT_AUTOREFERER, true);
               curl_setopt($ch, CURLOPT_COOKIESESSION,true);
               curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
               curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); 
            }
   			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects 
            $this->location = "";
      		
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'readHeader'));
			   curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.19) Gecko/2010031422 Firefox/3.0.19 ( .NET CLR 3.5.30729; .NET4.0E)');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
				curl_setopt($ch, CURLOPT_URL, $actualURL);

            ## Below two option will enable the HTTPS option.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);

            if (! empty($data) )
            {
               curl_setopt($ch, CURLOPT_POST, 1); 
               curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
            }
            // set this to true and run on the command line to see the debug output. it isn't really usefull
            if ($this->debug)
            {
               curl_setopt($ch, CURLOPT_VERBOSE, true); 
            }
            if (is_array($this->headers))
            {
               curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers); 
            }

				curl_multi_add_handle($mcurl, $ch);
				$threadsRunning++;
				$curlMap[$ch] = $url;
			}
			// Check if done
			if ($threadsRunning == 0 && $urls_id >= count($urls))
			{
				break;
			}

			// Let mcurl do it's thing
			curl_multi_select($mcurl);
			while(($mcRes = curl_multi_exec($mcurl, $mcActive)) == CURLM_CALL_MULTI_PERFORM) usleep(100000);
			if($mcRes != CURLM_OK) break;
			while($done = curl_multi_info_read($mcurl)) 
			{
				$ch = $done['handle'];
				$html = curl_multi_getcontent($ch);
				$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

				if(curl_errno($ch) == 0) 
				{
               if ($this->debug)
               {
                  file_put_contents("lastfetch.html",$html);
               }
					call_user_func($callback,array($url,$html));
					flush();
				} 
				else 
				{
					// echo "Link <a href='$done_url'>$done_url</a> failed: ".curl_error($ch)."<br>\n";
					flush();
				}
				curl_multi_remove_handle($mcurl, $ch);
				curl_close($ch);
				$threadsRunning--;
			}
		}
		curl_multi_close($mcurl);
	}


	/**
	 * CURL callback function for reading and processing headers
	 * Override this for your needs
	 * 
	 * @param object $ch
	 * @param string $header
	 * @return integer
	 */
	private function readHeader($ch, $header)
	{
		// we have to follow 302s automatically or cookies get lost.
		if (eregi("Location:",$header) )
		{
			$this->location= substr($header,strlen("Location: "));
		}
      #echo "$header\n";
		
		return strlen($header);
	}
}